<?php

namespace Laravel\Spark\Interactions\Settings\Profile;

use Illuminate\Support\Facades\Validator;
use Laravel\Spark\Events\Profile\ContactInformationUpdated;
use Laravel\Spark\Contracts\Interactions\Settings\Profile\UpdateContactInformation as Contract;
use Laravel\Spark\Spark;

class UpdateContactInformation implements Contract
{
    /**
     * {@inheritdoc}
     */
    public function validator($user, array $data)
    {
        $validator = Validator::make($data, [
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
        ]);

        $validator->after(function ($validator) use ($data, $user) {
            if (isset($data['email']) && $data['email'] != $user->email && in_array($data['email'], Spark::$developers)) {
                $validator->errors()->add('email', __('Email address is reserved.'));
            }
        });

        return $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function handle($user, array $data)
    {
        $user->forceFill([
            'name' => $data['name'],
            'email' => $data['email'],
        ])->save();

        event(new ContactInformationUpdated($user));

        return $user;
    }
}
