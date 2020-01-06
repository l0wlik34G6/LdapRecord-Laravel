<?php

namespace LdapRecord\Laravel\Listeners;

use Illuminate\Support\Facades\Auth;
use LdapRecord\Laravel\Traits\HasLdapUser;
use LdapRecord\Laravel\Auth\LdapAuthenticatable;

class BindsLdapUserModel
{
    /**
     * Binds the LDAP user record to their model.
     *
     * @param \Illuminate\Auth\Events\Login|\Illuminate\Auth\Events\Authenticated $event
     *
     * @return void
     */
    public function handle($event)
    {
        /** @var \LdapRecord\Laravel\Auth\UserProvider $provider */
        if ($provider = $this->getAuthProvider($event->guard)) {
            if ($event->user instanceof LdapAuthenticatable && $this->canBind($event->user)) {
                $event->user->setLdapUser(
                    $provider->getLdapUserRepository()->findByModel($event->user)
                );
            }
        }
    }

    /**
     * Get the authentication user provider.
     *
     * @param string $guard
     *
     * @return \Illuminate\Contracts\Auth\UserProvider|null
     */
    protected function getAuthProvider($guard)
    {
        return Auth::createUserProvider(
            config("auth.guards.$guard.provider")
        );
    }

    /**
     * Determines if we're able to bind to the user.
     *
     * @param LdapAuthenticatable $user
     *
     * @return bool
     */
    protected function canBind(LdapAuthenticatable $user)
    {
        return array_key_exists(HasLdapUser::class, class_uses_recursive($user)) && is_null($user->ldap);
    }
}
