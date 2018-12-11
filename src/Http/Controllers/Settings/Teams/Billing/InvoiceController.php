<?php

namespace Laravel\Spark\Http\Controllers\Settings\Teams\Billing;

use Laravel\Spark\Team;
use Laravel\Spark\Spark;
use Illuminate\Http\Request;
use Laravel\Spark\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all of the invoices for the given team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Spark\Team  $team
     * @return \Illuminate\Http\Response
     */
    public function all(Request $request, Team $team)
    {
        abort_unless($request->user()->ownsTeam($team), 403);

        if (! $team->hasBillingProvider()) {
            return [];
        }

        return $team->localInvoices;
    }

    /**
     * Download the invoice with the given ID.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Laravel\Spark\Team  $team
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function download(Request $request, Team $team, $id)
    {
        abort_unless($request->user()->ownsTeam($team), 403);

        $invoice = $team->localInvoices()
                            ->where('id', $id)->firstOrFail();

        return $team->downloadInvoice(
            $invoice->provider_id, ['id' => $invoice->id] + Spark::invoiceDataFor($team)
        );
    }
}
