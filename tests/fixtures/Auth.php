<?php namespace Bedard\Shop\Tests\Fixtures;

use Backend\Models\User;
use BackendAuth;

class Auth {

    /**
     * Creates a user for tests, and logs in as that user
     *
     * @param   array       $permissions
     * @param   string      $name
     * @param   string      $email
     * @param   string      $password
     * @return  User
     */
    public static function createUser($permissions = [], $name = 'test', $email = 'foo@bar.com', $password = 'test')
    {
        $user = User::create([
            'login'                 => $name,
            'email'                 => $email,
            'password'              => $password,
            'password_confirmation' => $password,
            'permissions'           => $permissions,
        ]);

        BackendAuth::authenticate([
            'login' => $name,
            'password' => $password
        ], true);

        return $user;
    }

    /**
     * Refreshes the user's session. If no password is provided,
     * we will assume that it's the same as the username.
     *
     * @param   User        $user
     * @param   string      $password
     * @return  User
     */
    public static function refreshUser(User $user, $password = false)
    {
        BackendAuth::authenticate([
            'login' => $user->login,
            'password' => $password ?: $user->login
        ], true);

        return $user;
    }

    /**
     * Changes a user's permissions, and refreshes their session
     *
     * @param   User        $user
     * @param   array       $permissions
     * @param   string      $password
     * @return  User
     */
    public static function setPermissions(User $user, array $permissions, $password = false)
    {
        $user->permissions = $permissions;
        $user->save();

        return self::refreshUser($user, $password);
    }
}
