@component('mail::message')
    <div class="wrap-order">
        <p>
            Company Name: <span style="text-decoration:underline;">{{$data->customer->name}}</span>
        </p>
        <p>
            Order Number: <span style="text-decoration:underline;">{{'CDKeyci ' .  $data->order_code  }}</span>
        </p>
        @foreach($data->orderItems as  $orderItem)
            <p style="font-size:13px;">{{$orderItem->game->name}} : {{$orderItem->quantity}} Units</p>
        @endforeach
    </div>
@endcomponent

