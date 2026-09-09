<?php

namespace Illuminate\Http;

interface Request
{
    /**
     * @return \Modules\AdminUser\Models\User|null
     */
    public function user($guard = null);
}