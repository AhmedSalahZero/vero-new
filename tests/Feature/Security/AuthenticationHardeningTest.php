<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;

class AuthenticationHardeningTest extends SecurityTestCase
{
	private Company $company;
	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		RateLimiter::clear('');
		$this->company = $this->makeCompany('Auth Co');
		$this->user = $this->makeUser(User::USER, [$this->company]);
	}

	public function test_public_registration_is_closed(): void
	{
		$before = User::count();
		$this->get('/register')->assertNotFound();
		$this->post('/register', [
			'name' => 'Stranger', 'email' => 'stranger-'.uniqid().'@example.test',
			'password' => 'Password123', 'password_confirmation' => 'Password123',
		])->assertNotFound();
		$this->assertSame($before, User::count());
		$this->assertGuest();
	}

	public function test_invalid_credentials_are_rejected(): void
	{
		$this->post('/login', ['email' => $this->user->email, 'password' => 'wrong-password'])->assertSessionHasErrors('email');
		$this->assertGuest();
		$this->post('/login', ['email' => "' OR '1'='1", 'password' => "' OR '1'='1"])->assertSessionHasErrors('email');
		$this->assertGuest();
	}

	public function test_login_is_rate_limited_even_for_the_correct_password(): void
	{
		for ($i = 0; $i < 5; $i++) {
			$this->post('/login', ['email' => $this->user->email, 'password' => 'wrong-'.$i]);
		}
		$response = $this->post('/login', ['email' => $this->user->email, 'password' => 'Secret-for-tests-1']);
		$response->assertSessionHasErrors('email');
		$this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
		$this->assertGuest();
	}

	public function test_session_id_is_regenerated_on_login(): void
	{
		$this->get('/login');
		$before = $this->app['session.store']->getId();

		$this->post('/login', ['email' => $this->user->email, 'password' => 'Secret-for-tests-1'])->assertRedirect();
		$this->assertAuthenticatedAs($this->user);
		$this->assertNotSame($before, $this->app['session.store']->getId(), 'session fixation: id must change on login');
	}

	public function test_logout_invalidates_the_session(): void
	{
		$this->post('/login', ['email' => $this->user->email, 'password' => 'Secret-for-tests-1']);
		$this->assertAuthenticatedAs($this->user);
		$this->app['session.store']->put('security-marker', 'secret');
		$sessionBefore = $this->app['session.store']->getId();
		$tokenBefore = $this->app['session.store']->token();

		$this->post('/logout')->assertRedirect();

		$this->assertGuest();
		$this->assertNull($this->app['session.store']->get('security-marker'));
		$this->assertNotSame($sessionBefore, $this->app['session.store']->getId());
		$this->assertNotSame($tokenBefore, $this->app['session.store']->token(), 'CSRF token must be rotated');
		$this->get(route('home'))->assertRedirect(route('login'));
	}

	public function test_expired_trial_account_is_not_left_logged_in(): void
	{
		$this->user->forceFill(['subscription' => 'free_trial', 'expiration_date' => now()->subDay()->toDateString()])->save();

		$this->post('/login', ['email' => $this->user->email, 'password' => 'Secret-for-tests-1'])->assertRedirect(route('login'));
		$this->assertGuest();
	}

	public function test_password_reset_does_not_reveal_which_emails_exist(): void
	{
		Notification::fake();

		$known = $this->post('/password/email', ['email' => $this->user->email]);
		$unknown = $this->post('/password/email', ['email' => 'nobody-'.uniqid().'@example.test']);

		$known->assertSessionHasNoErrors();
		$unknown->assertSessionHasNoErrors();
		$this->assertSame($known->getSession()->get('status'), $unknown->getSession()->get('status'));
		$this->assertSame(trans('passwords.sent'), $unknown->getSession()->get('status'));
		Notification::assertSentTo($this->user, ResetPassword::class);
	}

	public function test_password_reset_endpoints_are_throttled(): void
	{
		Notification::fake();
		for ($i = 0; $i < 5; $i++) {
			$this->post('/password/email', ['email' => 'probe-'.$i.'@example.test'])->assertStatus(302);
		}
		$this->post('/password/email', ['email' => 'probe-6@example.test'])->assertStatus(429);

		for ($i = 0; $i < 5; $i++) {
			$this->post('/password/reset', ['token' => 'guess-'.$i, 'email' => $this->user->email, 'password' => 'NewPassword1', 'password_confirmation' => 'NewPassword1']);
		}
		$this->post('/password/reset', ['token' => 'guess-6', 'email' => $this->user->email, 'password' => 'NewPassword1', 'password_confirmation' => 'NewPassword1'])->assertStatus(429);
	}

	public function test_csrf_protection_has_no_exclusions(): void
	{
		$middleware = new \App\Http\Middleware\VerifyCsrfToken($this->app, $this->app['encrypter']);
		$this->assertSame([], (new \ReflectionProperty($middleware, 'except'))->getValue($middleware));
	}
}
