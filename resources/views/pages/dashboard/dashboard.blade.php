@extends('layouts.app')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
  <div class="col-span-12 space-y-6">
    <x-ecommerce.ecommerce-metrics 
      :total="$totalWarga"
      :today="$totalToday"
      :militan="$Militan"
      :ngambang="$Ngambang"
      :lawan="$Lawan"
       />
  </div>
</div>
@endsection