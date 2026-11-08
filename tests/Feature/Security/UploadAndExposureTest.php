<?php

namespace Tests\Feature\Security;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * File upload validation, credential exposure in HTML and security headers.
 */
class UploadAndExposureTest extends SecurityTestCase
{
	private Company $company;
	private User $admin;

	protected function setUp(): void
	{
		parent::setUp();
		$this->company = $this->makeCompany('Upload Co');
		$this->admin = $this->makeUser(User::COMPANY_ADMIN, [$this->company]);
	}

	private function uploadLogo(UploadedFile $file)
	{
		return $this->actAs($this->admin)->postJson(route('save.labeling.item', ['company' => $this->company->id]), [
			'labeling_type' => 'qr', 'labeling_client_logo' => $file,
		]);
	}

	/**
	 * A real temporary file, so the MIME type is sniffed from the CONTENT exactly
	 * like a browser upload (UploadedFile::fake() derives it from the filename).
	 */
	private function realUpload(string $clientName, string $content): UploadedFile
	{
		$path = tempnam(sys_get_temp_dir(), 'sec');
		file_put_contents($path, $content);

		return new UploadedFile($path, $clientName, null, null, true);
	}

	public function test_php_payload_disguised_as_image_is_rejected_and_not_stored(): void
	{
		Storage::fake('public');
		$this->uploadLogo($this->realUpload('logo.png', '<?php system($_GET["c"]); ?>'))->assertStatus(422);
		$this->uploadLogo($this->realUpload('shell.php', '<?php phpinfo(); ?>'))->assertStatus(422);
		$this->assertSame([], Storage::disk('public')->allFiles());
		$this->assertNull($this->company->fresh()->labeling_client_logo);
	}

	public function test_image_polyglots_can_never_be_stored_with_an_executable_extension(): void
	{
		// A GIF header followed by PHP is a valid image to libmagic, so it may be
		// accepted — but the stored name comes from the sniffed type (.gif), never
		// from the client, so it cannot be executed by the PHP handler.
		Storage::fake('public');
		$this->uploadLogo($this->realUpload('shell.php.jpg', "GIF89a<?php system(\$_GET['c']); ?>"));
		foreach (Storage::disk('public')->allFiles() as $file) {
			$this->assertMatchesRegularExpression('/\.(gif|png|jpe?g|webp)$/i', $file);
			$this->assertDoesNotMatchRegularExpression('/\.(php\d?|phtml|phar)/i', $file);
		}
	}

	public function test_svg_and_html_uploads_are_rejected(): void
	{
		Storage::fake('public');
		$this->uploadLogo($this->realUpload('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"/>'))->assertStatus(422);
		$this->uploadLogo($this->realUpload('logo.html', '<html><script>alert(1)</script></html>'))->assertStatus(422);
		$this->assertSame([], Storage::disk('public')->allFiles());
	}

	public function test_real_images_are_still_accepted(): void
	{
		Storage::fake('public');
		$this->uploadLogo(UploadedFile::fake()->image('logo.png', 40, 40))->assertOk();
		$stored = $this->company->fresh()->labeling_client_logo;
		$this->assertNotNull($stored);
		$this->assertStringEndsWith('.png', $stored);
		Storage::disk('public')->assertExists($stored);
	}

	public function test_odoo_password_is_never_rendered_back(): void
	{
		$this->company->forceFill(['odoo_db_url' => 'http://127.0.0.1:9', 'odoo_db_name' => 'db'])->save();
		$this->admin->forceFill(['odoo_username' => 'odoo-user', 'odoo_db_password' => 'Super-Secret-Odoo-Key'])->save();
		$this->admin = $this->admin->fresh(['roles', 'permissions', 'companies']);

		$html = $this->actAs($this->admin)->get(route('profile.edit'))->assertOk()->getContent();
		$this->assertStringNotContainsString('Super-Secret-Odoo-Key', $html);
		$this->assertMatchesRegularExpression('/<input[^>]+type="password"[^>]+name="odoo_db_password"/', $html);
	}

	public function test_security_headers_are_sent(): void
	{
		$response = $this->get('/login');
		$response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
		$response->assertHeader('X-Content-Type-Options', 'nosniff');
		$response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
		$response->assertHeader('Permissions-Policy');
		$response->assertHeaderMissing('Strict-Transport-Security');

		$secure = $this->get('https://localhost/login');
		$secure->assertHeader('Strict-Transport-Security');
	}

	public function test_error_pages_do_not_leak_stack_traces_when_debug_is_off(): void
	{
		config(['app.debug' => false]);
		$response = $this->actAs($this->admin)->getJson(route('show.total.net.balance.in', [
			'company' => $this->company->id, 'currency' => 'EGP', 'modelType' => 'User',
		]));
		$response->assertNotFound();
		$this->assertArrayNotHasKey('trace', $response->json() ?? []);
		$this->assertArrayNotHasKey('exception', $response->json() ?? []);
	}
}
