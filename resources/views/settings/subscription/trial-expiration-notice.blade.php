@if(auth()->user()->subscription() && in_array(auth()->user()->subscription()->stripe_status, ['incomplete' , 'past_due']))
    <div class="alert alert-warning  mb-4">
        {!! __('Please :linkOpen confirm your payment :linkClose to activate your subscription!', ['linkOpen' => '<a href="/'.config('cashier.path').'/payment/'.auth()->user()->subscription()->latestPayment()->id.'?redirect=/home">', 'linkClose' => '</a>']) !!}
    </div>
@elseif(auth()->user()->currentteam() && auth()->user()->currentteam()->subscription() && in_array(auth()->user()->currentteam()->subscription()->stripe_status, ['incomplete' , 'past_due']))
    <div class="alert alert-warning  mb-4">
        {!! __('Please :linkOpen confirm your payment :linkClose to activate your subscription!', ['linkOpen' => '<a href="/'.config('cashier.path').'/payment/'.auth()->user()->currentteam()->subscription()->latestPayment()->id.'?redirect=/home">', 'linkClose' => '</a>']) !!}
    </div>
@else
    <div class="alert alert-warning mb-4" v-if="subscriptionIsOnTrial">
        <?php echo __('You are currently within your free trial period. Your trial will expire on :date.', ['date' => '<strong>{{ trialEndsAt }}</strong>']); ?>
    </div>
@endif