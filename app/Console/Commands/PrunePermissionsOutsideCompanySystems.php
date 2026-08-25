<?php

namespace App\Console\Commands;

use App\Helpers\HAuth;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * بيشيل صفوف الصلاحيات اللي بره انظمة شركة اليوزر.
 *
 * الصفوف دي بتتكتب لما حد يدي اليوزر الصلاحيات كلها bulk ، او لما انظمة
 * الشركة تتقفل بعد ما اليوزر كان واخد صلاحيات اوسع. الكود بقى بيفلتر عند
 * الفحص كمان (User::hasPermissionTo) ، بس الصفوف الزيادة بتفضل مربكة في
 * شاشة الصلاحيات نفسها.
 */
class PrunePermissionsOutsideCompanySystems extends Command
{
    protected $signature = 'permissions:prune-outside-systems
                            {--user=* : IDs محددة بدل كل اليوزرز}
                            {--dry-run : اعرض بس من غير ما تعدل}';

    protected $description = "Remove permission rows that don't belong to any system the user's company subscribes to";

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $userIds = array_filter((array) $this->option('user'));

        /*
         * non-banking-service و property-management ملهمش اي صلاحية في
         * HAuth::getPermissions ، فا اي تنضيف ليهم هيشيل كل حاجة. بنسيبهم.
         */
        $knownSystems = [];
        foreach (HAuth::getPermissions() as $permissionArr) {
            foreach ($permissionArr['systems'] as $system) {
                $knownSystems[$system] = true;
            }
        }
        $knownSystems = array_keys($knownSystems);

        $users = User::with('companies', 'permissions', 'roles')
            ->when($userIds, fn ($query) => $query->whereIn('id', $userIds))
            ->orderBy('id')
            ->get();

        $totalRemoved = 0;

        foreach ($users as $user) {
            if ($user->roles->isEmpty()) {
                $this->warn("#{$user->id} {$user->email} — no role, skipped");
                continue;
            }
            if ($user->isSuperAdmin()) {
                continue;
            }

            $company = $user->companies->first();
            if (! $company) {
                continue;
            }

            $companySystems = $company->getSystemsNames();
            if (! count($companySystems)) {
                $this->warn("#{$user->id} {$user->email} — company {$company->id} has no system, skipped");
                continue;
            }
            if (count(array_diff($companySystems, $knownSystems))) {
                $this->warn("#{$user->id} {$user->email} — company {$company->id} runs on ".implode(',', $companySystems).' (not described in the permission catalogue), skipped');
                continue;
            }

            $allowed = array_column(HAuth::getPermissions($companySystems), 'name');
            $current = $user->permissions->pluck('name')->toArray();
            $extra = array_values(array_diff($current, $allowed));

            if (! count($extra)) {
                continue;
            }

            $this->line("#{$user->id} {$user->email} — company {$company->id} (".implode(',', $companySystems).'): '.count($extra).' permission(s) to remove');
            if ($this->getOutput()->isVerbose()) {
                foreach ($extra as $permissionName) {
                    $this->line("    - {$permissionName}");
                }
            }

            $totalRemoved += count($extra);

            if (! $dryRun) {
                $user->syncPermissions(array_values(array_intersect($current, $allowed)));
            }
        }

        app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info($dryRun
            ? "Dry run: {$totalRemoved} permission row(s) would be removed."
            : "Removed {$totalRemoved} permission row(s).");

        return Command::SUCCESS;
    }
}
