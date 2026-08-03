<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use App\Services\Api\OdooService;
use App\Traits\ImageSave;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
	use ImageSave;

	public function edit()
	{
		$user = auth()->user();
		/**
		 * @var User $user
		 */
		$hasOdooCredentials = $user->companies->contains(fn (Company $company) => $company->hasOdooCredentials());

		return view('profile.form', compact('user', 'hasOdooCredentials'));
	}

	public function update(Request $request)
	{
		$user = auth()->user();
		/**
		 * @var User $user
		 */
		$request->validate([
			'name' => ['required', 'string', 'max:255'],
			'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
			'avatar' => ['nullable', 'image'],
			'odoo_username' => ['nullable', 'string', 'max:255'],
			'odoo_db_password' => ['nullable', 'string', 'max:255'],
		]);

		$user->update($request->only('name', 'email'));

		ImageSave::saveIfExist('avatar', $user);

		$hasOdooCredentials = $user->companies->contains(fn (Company $company) => $company->hasOdooCredentials());

		if ($hasOdooCredentials) {
			$odooCredentialsChanged = $request->odoo_username !== $user->odoo_username
				|| $request->odoo_db_password !== $user->odoo_db_password;

			$user->update([
				'odoo_username' => $request->odoo_username,
				'odoo_db_password' => $request->odoo_db_password,
			]);

			/**
			 * * بنجيبه كمان لما يكون فاضي أصلاً حتى لو الكريدنشيالز ما اتغيرتش
			 * * قبل كده لو اليوزر حفظ نفس البيانات كان مفيش حاجة بتحصل
			 * * فالـ odoo_id يفضل null ومفيش طريقة تخليه يترجع من الواجهة
			 */
			if ($odooCredentialsChanged || is_null($user->getOdooId())) {
				$this->refreshOdooId($user);
			}
		}

		toastr()->success(__('Updated Successfully'));

		return redirect()->back();
	}

	private function refreshOdooId(User $user): void
	{
		$odooCompany = $user->companies->first(fn (Company $company) => $company->hasOdooCredentials());

		if (!$odooCompany) {
			return;
		}

		/**
		 * * التصفير والمصادقة واللوج كلهم جوه الميثود دي
		 * * عشان تبقى نفس السلوك بتاع فورم الشركة
		 */
		OdooService::refreshUserOdooId($odooCompany, $user);
	}
}
