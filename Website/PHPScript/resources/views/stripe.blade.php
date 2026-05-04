
<div id="stripe_div" style="margin-top: 20%; " >
    <form action="{{url('stripe-callback')}}" method="post" id="stripe-form" class="center" style="margin:auto; width:15%">
        {{csrf_field()}}
        <input type="hidden" name="phone_no" id="phone_no_2">
        <input type="hidden" name="date" id="date_2">
        <input type="hidden" name="slot" id="slot_2">
        <input type="hidden" name="message" id="message_2">
        <input type="hidden" name="payment_type" value="Stripe">
         <input type="hidden" name="type" value="1">
        <input type="hidden" name="id" value="{{$data->id}}">
        <input type="hidden" name="doctor_id" id="doctor_id" value="{{$data->doctor_id}}">
        <input type="hidden" name="consultation_fees" value="{{$data->consultation_fees}}">
        <script
          src="https://checkout.stripe.com/checkout.js" class="stripe-button"
          data-key="{{env('STRIPE_KEY')}}"
          data-amount="{{$data->consultation_fees}}"
          data-id="stripid"
          data-name="{{__('message.System Name')}}"
          data-label="{{__('message.Book Appointment')}}"
          data-description=""
          data-image="{{asset('public/image_web/').'/'.$setting->logo}}"
          data-locale="auto">
        </script>
    </form> 
</div>  