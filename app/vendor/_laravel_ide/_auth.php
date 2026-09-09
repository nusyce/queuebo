<?php

namespace Illuminate\Contracts\Auth;

interface Guard
{
    /**
     * @return \Modules\AdminUser\Models\User|null
     */
    public function user();
}