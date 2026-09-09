<?php

namespace Modules\AdminPlans\Support;

use Modules\AdminPlans\Models\AdminPlan;

class DefaultSignupPlanResolver
{
    public function resolve(): ?AdminPlan
    {
        return AdminPlan::query()
            ->where('status', true)
            ->where('free_plan', true)
            ->orderByDesc('default_signup_plan')
            ->orderByDesc('featured')
            ->orderBy('position')
            ->orderBy('id')
            ->first();
    }
}

