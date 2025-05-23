<?php

namespace Database\Seeders;

use Crater\Models\Company;
use Crater\Models\Setting;
use Crater\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Silber\Bouncer\BouncerFacade;
use Vinkla\Hashids\Facades\Hashids;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Busca o crea el usuario admin
        $user = User::firstOrCreate(
            ['email' => 'admin@craterapp.com'],
            [
                'name' => 'Jane Doe',
                'role' => 'super admin',
                'password' => Hash::make('crater@123'),
            ]
        );

        // Si el usuario ya tiene empresas, no crear otra
        if ($user->companies()->count() === 0) {
            $company = Company::create([
                'name' => 'xyz',
                'owner_id' => $user->id,
                'slug' => 'xyz'
            ]);

            $company->unique_hash = Hashids::connection(Company::class)->encode($company->id);
            $company->save();

            $company->setupDefaultData();
            $user->companies()->attach($company->id);
        } else {
            $company = $user->companies()->first();
        }

        BouncerFacade::scope()->to($company->id);
        $user->assign('super admin');

        Setting::setSetting('profile_complete', 0);
    }
}