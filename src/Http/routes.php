<?php

$router->group(['middleware' => 'web'], function ($router) {
    // Terms Of Service...
    $router->get('/terms', 'TermsController@show')->name('terms');

    // Customer Support...
    $router->post('/support/email', 'SupportController@sendEmail');

    // API Token Refresh...
    $router->put('/spark/token', 'TokenController@refresh');

    // Users...
    $router->get('/user/current', 'UserController@current');
    $router->put('/user/last-read-announcements-at', 'UserController@updateLastReadAnnouncementsTimestamp');

    // Notifications
    $router->get('/notifications/recent', 'NotificationController@recent');
    $router->put('/notifications/read', 'NotificationController@markAsRead');

    // Settings Dashboard...
    $router->get('/settings', 'Settings\DashboardController@show')->name('settings');

    // Profile Contact Information...
    $router->put('/settings/contact', 'Settings\Profile\ContactInformationController@update');

    // Profile Photo...
    $router->post('/settings/photo', 'Settings\Profile\PhotoController@store');

    // Teams...
    if (Spark::usesTeams()) {
        // Missing Team Notice...
        $router->get('/'.Spark::teamsPrefix().'/missing', 'MissingTeamController@show');

        // General Settings...
        $router->get('/settings/'.Spark::teamsPrefix().'/roles', 'Settings\Teams\TeamMemberRoleController@all');
        $router->get('/settings/'.Spark::teamsPrefix().'/current', 'TeamController@current');
        $router->get('/settings/'.Spark::teamsPrefix().'/json/{team_id}', 'TeamController@show');

        $router->get('/settings/'.Spark::teamsPrefix().'/{team}', 'Settings\Teams\DashboardController@show')->name('settings.team');
        $router->post('/settings/'.Spark::teamsPrefix().'/{team}/photo', 'Settings\Teams\TeamPhotoController@update');
        $router->put('/settings/'.Spark::teamsPrefix().'/{team}/name', 'Settings\Teams\TeamNameController@update');
        $router->get('/settings/'.Spark::teamsPrefix().'/{team}/invitations', 'Settings\Teams\MailedInvitationController@all');
        $router->post('/settings/'.Spark::teamsPrefix().'/{team}/invitations', 'Settings\Teams\MailedInvitationController@store');
        $router->put('/settings/'.Spark::teamsPrefix().'/{team}/members/{team_member}', 'Settings\Teams\TeamMemberController@update');
        $router->delete('/settings/'.Spark::teamsPrefix().'/{team}/members/{team_member}', 'Settings\Teams\TeamMemberController@destroy');
        $router->delete('/settings/'.Spark::teamsPrefix().'/{team}', 'Settings\Teams\TeamController@destroy');
        $router->get('/settings/'.Spark::teamsPrefix().'/{team}/switch', 'TeamController@switchCurrentTeam');

        $router->get('/settings/'.Spark::teamsPrefix(), 'TeamController@all');
        $router->post('/settings/'.Spark::teamsPrefix(), 'Settings\Teams\TeamController@store');

        $router->get('/settings/invitations/pending', 'Settings\Teams\PendingInvitationController@all');
        $router->get('/invitations/{invitation}', 'InvitationController@show');
        $router->post('/settings/invitations/{invitation}/accept', 'Settings\Teams\PendingInvitationController@accept');
        $router->post('/settings/invitations/{invitation}/reject', 'Settings\Teams\PendingInvitationController@reject');
        $router->delete('/settings/invitations/{invitation}', 'Settings\Teams\MailedInvitationController@destroy');

        // Billing

        // Subscription Settings...
        $router->post('/settings/'.Spark::teamsPrefix().'/{team}/subscription', 'Settings\Teams\Subscription\PlanController@store');
        $router->put('/settings/'.Spark::teamsPrefix().'/{team}/subscription', 'Settings\Teams\Subscription\PlanController@update');
        $router->delete('/settings/'.Spark::teamsPrefix().'/{team}/subscription', 'Settings\Teams\Subscription\PlanController@destroy');

        // VAT ID Settings...
        $router->put('/settings/'.Spark::teamsPrefix().'/{team}/payment-method/vat-id', 'Settings\Teams\PaymentMethod\VatIdController@update');

        // Credit Card Settings...
        $router->put('/settings/'.Spark::teamsPrefix().'/{team}/payment-method', 'Settings\Teams\PaymentMethod\PaymentMethodController@update');

        // Redeem Coupon...
        $router->post('/settings/'.Spark::teamsPrefix().'/{team}/payment-method/coupon', 'Settings\Teams\PaymentMethod\RedeemCouponController@redeem');

        // Billing History...
        $router->put(
            '/settings/'.Spark::teamsPrefix().'/{team}/extra-billing-information',
            'Settings\Teams\Billing\BillingInformationController@update'
        );

        // Coupons...
        $router->get('/settings/'.Spark::teamsPrefix().'/coupon/{id}', 'TeamCouponController@current');

        // Invoices...
        $router->get('/settings/'.Spark::teamsPrefix().'/{team}/invoices', 'Settings\Teams\Billing\InvoiceController@all');
        $router->get('/settings/'.Spark::teamsPrefix().'/{team}/invoice/{id}', 'Settings\Teams\Billing\InvoiceController@download');
    }

    // Security Settings...
    $router->put('/settings/password', 'Settings\Security\PasswordController@update');
    $router->post('/settings/two-factor-auth', 'Settings\Security\TwoFactorAuthController@enable');
    $router->delete('/settings/two-factor-auth', 'Settings\Security\TwoFactorAuthController@disable');

    // API Settings
    $router->get('/settings/api/tokens', 'Settings\API\TokenController@all');
    $router->post('/settings/api/token', 'Settings\API\TokenController@store');
    $router->put('/settings/api/token/{token_id}', 'Settings\API\TokenController@update');
    $router->get('/settings/api/token/abilities', 'Settings\API\TokenAbilitiesController@all');
    $router->delete('/settings/api/token/{token_id}', 'Settings\API\TokenController@destroy');

    // Plans...
    $router->get('/spark/plans', 'PlanController@all');

    // Subscription Settings...
    $router->post('/settings/subscription', 'Settings\Subscription\PlanController@store');
    $router->put('/settings/subscription', 'Settings\Subscription\PlanController@update');
    $router->delete('/settings/subscription', 'Settings\Subscription\PlanController@destroy');

    // VAT ID Settings...
    $router->put('/settings/payment-method/vat-id', 'Settings\PaymentMethod\VatIdController@update');

    // Credit Card Settings...
    $router->put('/settings/payment-method', 'Settings\PaymentMethod\PaymentMethodController@update');

    // Redeem Coupon...
    $router->post('/settings/payment-method/coupon', 'Settings\PaymentMethod\RedeemCouponController@redeem');

    // Billing History...
    $router->put(
        '/settings/extra-billing-information',
        'Settings\Billing\BillingInformationController@update'
    );

    // Invoices...
    $router->get('/settings/invoices', 'Settings\Billing\InvoiceController@all');
    $router->get('/settings/invoice/{id}', 'Settings\Billing\InvoiceController@download');

    // Coupons...
    $router->get('/coupon/user/{id}', 'CouponController@current');
    $router->get('/coupon/{code}', 'CouponController@show');

    // Kiosk...
    $router->get('/spark/kiosk', 'Kiosk\DashboardController@show')->name('kiosk');

    // Kiosk Search...
    $router->post('/spark/kiosk/users/search', 'Kiosk\SearchController@performBasicSearch');

    // Kiosk Announcements...
    $router->get('/spark/kiosk/announcements', 'Kiosk\AnnouncementController@all');
    $router->post('/spark/kiosk/announcements', 'Kiosk\AnnouncementController@store');
    $router->put('/spark/kiosk/announcements/{id}', 'Kiosk\AnnouncementController@update');
    $router->delete('/spark/kiosk/announcements/{id}', 'Kiosk\AnnouncementController@destroy');

    // Kiosk Metrics / Performance Indicators...
    $router->get('/spark/kiosk/performance-indicators', 'Kiosk\PerformanceIndicatorsController@all');
    $router->get('/spark/kiosk/performance-indicators/revenue', 'Kiosk\PerformanceIndicatorsController@revenue');
    $router->get('/spark/kiosk/performance-indicators/plans', 'Kiosk\PerformanceIndicatorsController@subscribers');
    $router->get('/spark/kiosk/performance-indicators/trialing', 'Kiosk\PerformanceIndicatorsController@trials');

    // Kiosk User Profiles...
    $router->get('/spark/kiosk/users/{id}/profile', 'Kiosk\ProfileController@show');

    // Kiosk Discounts...
    $router->post('/spark/kiosk/users/discount/{id}', 'Kiosk\DiscountController@store');

    // Kiosk Impersonation...
    $router->get('/spark/kiosk/users/impersonate/{id}', 'Kiosk\ImpersonationController@impersonate');
    $router->get('/spark/kiosk/users/stop-impersonating', 'Kiosk\ImpersonationController@stopImpersonating');

    // Authentication...
    $router->get('/login', 'Auth\LoginController@showLoginForm')->name('login');
    $router->post('/login', 'Auth\LoginController@login');
    $router->get('/logout', 'Auth\LoginController@logout')->name('logout');

    // Two-Factor Authentication Routes...
    $router->get('/login/token', 'Auth\LoginController@showTokenForm');
    $router->post('/login/token', 'Auth\LoginController@verifyToken');

    // Two-Factor Emergency Token Login Routes...
    $router->get('/login-via-emergency-token', 'Auth\EmergencyLoginController@showLoginForm');
    $router->post('/login-via-emergency-token', 'Auth\EmergencyLoginController@login');

    // Registration...
    $router->get('/register', 'Auth\RegisterController@showRegistrationForm')->name('register');
    $router->post('/register', 'Auth\RegisterController@register');

    // Password Reset...
    $router->get('/password/reset/{token?}', 'Auth\PasswordController@showResetForm')->name('password.reset');
    $router->post('/password/email', 'Auth\PasswordController@sendResetLinkEmail');
    $router->post('/password/reset', 'Auth\PasswordController@reset');
});

// Tax Rates...
$router->post('/tax-rate', 'TaxRateController@calculate');

// Geocoding...
$router->get('/geocode/country', 'GeocodingController@country');
$router->get('/geocode/states/{country}', 'GeocodingController@states');

// Webhooks...
$router->post('/webhook/stripe', 'Settings\Billing\StripeWebhookController@handleWebhook');
$router->post('/webhook/braintree', 'Settings\Billing\BraintreeWebhookController@handleWebhook');
