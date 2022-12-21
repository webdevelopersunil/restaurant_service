<x-master-layout>
    <div class="container-fluid">
        <div class="row">
            <div class="modal fade" id="adminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="adminModalLabel">{{__('messages.dashboard_customizer')}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                    {{ Form::open(['method' => 'POST','route' => 'togglesetting']) }}

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Top_Cards)) || $show == "true")
                            {{Form::checkbox('Top Cards', "top_card",true)}} {{__('messages.dashboard_card_title',['name' => __('messages.top')] )}}<br>
                        @else
                            {{Form::checkbox('Top Cards', "top_card",false)}} {{__('messages.dashboard_card_title',['name' => __('messages.top')] )}}<br>
                        @endif

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Monthly_Revenue_card)) || $show == "true")
                            {{Form::checkbox('Monthly Revenue card', "monthly_revenue_card", true)}} {{__('messages.dashboard_card_title',['name' => __('messages.monthly_revenue')] )}}<br>
                        @else
                            {{Form::checkbox('Monthly Revenue card', "monthly_revenue_card", false)}} {{__('messages.dashboard_card_title',['name' => __('messages.monthly_revenue')] )}}<br>
                        @endif

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Top_Services_card)) || $show == "true")
                            {{Form::checkbox('Top Services card', "top_service_card", true)}} {{__('messages.dashboard_card_title',['name' => __('messages.top_services')] )}}<br>
                        @else
                            {{Form::checkbox('Top Services card', "top_service_card", false)}} {{__('messages.dashboard_card_title',['name' => __('messages.top_services')] )}}<br>
                        @endif

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->New_Provider_card)) || $show == "true")
                            {{Form::checkbox('New Provider card', "new_provider_card", true)}} {{__('messages.dashboard_card_title',['name' => __('messages.top_services')] )}}<br>
                        @else
                            {{Form::checkbox('New Provider card', "new_provider_card", false)}} {{__('messages.dashboard_card_title',['name' => __('messages.top_services')] )}}<br>
                        @endif

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Upcoming_Booking_card)) || $show == "true")
                            {{Form::checkbox('Upcoming Booking card', "upcoming_booking_card", true)}} {{__('messages.dashboard_card_title',['name' => __('messages.dashboard_upcomming_booking')] )}}<br>
                        @else
                            {{Form::checkbox('Upcoming Booking card', "upcoming_booking_card", false)}} {{__('messages.dashboard_card_title',['name' => __('messages.dashboard_upcomming_booking')] )}}<br>
                        @endif

                        @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->New_Customer_card)) || $show == "true")
                            {{Form::checkbox('New Customer card', "new_customer_card", true)}} {{__('messages.dashboard_card_title',['name' => __('messages.new_customer')] )}}<br>
                        @else
                            {{Form::checkbox('New Customer card', "new_customer_card", false)}} {{__('messages.dashboard_card_title',['name' => __('messages.new_customer')] )}}<br>
                        @endif

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        {{ Form::submit('Save', ['class' => 'btn btn-primary']) }}
                    {{ Form::close() }}
                    </div>
                    </div>
                </div>
            </div>
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Top_Cards)) || $show == "true")
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <div class="d-flex flex-wrap justify-content-start align-items-center">
                                            <h5 class="mb-2 text-primary font-weight-bold">{{ !empty($data['dashboard']['count_total_booking']) ? $data['dashboard']['count_total_booking']: 0 }} </h5>
                                        </div>
                                        <p class="mb-0 text-secondary">{{ __('messages.total_name', ['name' => __('messages.booking')]) }}</p>
                                    </div>
                                    <div class="col-auto d-flex flex-column">
                                        <div class="iq-card-icon icon-shape bg-soft-primary text-primary rounded-circle shadow">
                                            <i class="ri-calendar-check-line"></i>
                                        </div>
                                        <a class="pt-2" href="">{{__('messages.view_all')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <div class="d-flex flex-wrap justify-content-start align-items-center">
                                            <h5 class="mb-2 text-primary font-weight-bold">{{ !empty($data['dashboard']['count_total_service']) ? $data['dashboard']['count_total_service'] : 0 }}</h5>
                                        </div>
                                        <p class="mb-0 text-secondary">{{ __('messages.total_name', ['name' => __('messages.service')]) }}</p>
                                    </div>
                                    <div class="col-auto d-flex flex-column">
                                        <div class="iq-card-icon icon-shape bg-soft-warning text-white rounded-circle shadow">
                                            <i class="ri-service-line"></i>
                                        </div>
                                        <a class="pt-2" href="{{ route('service.index') }}">{{__('messages.view_all')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <div class="d-flex flex-wrap justify-content-start align-items-center">
                                            <h5 class="mb-2 text-primary font-weight-bold">{{ !empty($data['dashboard']['count_total_provider']) ? $data['dashboard']['count_total_provider'] : 0 }}</h5>
                                            <p class="mb-0 ml-3 text-danger font-weight-bold"></p>
                                        </div>
                                        <p class="mb-0 text-secondary">{{ __('messages.total_name', ['name' => __('messages.provider')]) }}</p>
                                    </div>
                                    <div class="col-auto d-flex flex-column">
                                        <div class="iq-card-icon icon-shape bg-success text-white rounded-circle shadow">
                                            <i class="la la-users"></i>
                                        </div>
                                        <a class="pt-2" href="{{ route('provider.index') }}">{{__('messages.view_all')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col">
                                        <div class="d-flex flex-wrap justify-content-start align-items-center">
                                            <h5 class="mb-2 text-primary font-weight-bold">{{ getPriceFormat(round($data['total_revenue'])) }}</h5>
                                            <p class="mb-0 ml-3 text-danger font-weight-bold"></p>
                                        </div>
                                        <p class="mb-0 text-secondary">{{ __('messages.total_name', ['name' => __('messages.revenue')]) }}</p>
                                    </div>
                                    <div class="col-auto d-flex flex-column">
                                        <div class="iq-card-icon icon-shape bg-success text-white rounded-circle shadow">
                                            <i class="ri-secure-payment-line"></i>
                                        </div>
                                        <a class="pt-2" href="{{ route('payment.index') }}">{{__('messages.view_all')}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Monthly_Revenue_card)) || $show == "true")
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h4 class="font-weight-bold">{{__('messages.monthly_revenue')}}</h4>
                        </div>
                        <div id="monthly-revenue" class="custom-chart"></div>
                    </div>
                </div>
            </div>
            @endif
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Top_Services_card)) || $show == "true")
            <div class="col-md-6">
                <div class="card card-block card-height">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="font-weight-bold">{{ __('messages.top_services') }}</h5>
                        <a href="{{ route('service.index') }}" class="float-right mr-1 btn btn-sm btn-primary">{{ __('messages.see_all') }}</a>
                    </div>
                    <div class="card-body-list">
                        <table class="table table-spacing mb-0">
                            <tbody>
                                @if(count($data['dashboard']['top_services_list']) > 0)
                                    @foreach($data['dashboard']['top_services_list'] as $services)

                                        @php
                                            $image = getSingleMedia($services->service,'service_attachment', null);
                                            $file_extention = config('constant.IMAGE_EXTENTIONS');
                                            $extention = in_array(strtolower(imageExtention($image)),$file_extention);
                                        @endphp
                                        <tr class="white-space-no-wrap">
                                            <td class="p-2">
                                                <div class="active-project-1 d-flex align-items-center mt-0 ">
                                                    <div class="h-avatar is-medium">
                                                        @if($extention)
                                                            <img class="avatar rounded-circle" alt="user-icon" src="{{ $image }}">
                                                        @else
                                                            <img class="avatar rounded-circle" alt="user-icon" src="{{ asset('images/file.png') }}">
                                                        @endif
                                                    </div>
                                                    <div class="data-content">
                                                        <div>
                                                            <span class="font-weight-bold">{{optional($services->service)->name ?? '-'}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>

                                            <td class="pl-0 py-3">
                                                {{getPriceFormat(optional($services->service)->price ?? 0)}}
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                <div class="data-not-found">{{__('messages.not_found_entry',['name' => __('messages.service')] )}}</div>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->New_Provider_card)) || $show == "true")
            <div class="col-md-6">
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="font-weight-bold">{{ __('messages.new_provider') }}</h4>
                        <a href="{{ route('provider.index') }}" class="float-right mr-1 btn btn-sm btn-primary">{{ __('messages.see_all') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive max-height-400">
                            <table class="table mb-0">
                                <thead class="table-color-heading">
                                <tr class="text-secondary">
                                    <th scope="col">{{__('messages.date')}}</th>
                                    <th scope="col">{{__('messages.user')}}</th>
                                    <th scope="col">{{__('messages.email')}}</th>
                                    <th scope="col" class="white-space-no-wrap">{{__('messages.contact_number')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($data['dashboard']['new_provider'] as $provider)
                                    <tr class="white-space-no-wrap">
                                        <td> {{date("d M Y", strtotime($provider->created_at))}}</td>
                                        <td>
                                            <div class="active-project-1 d-flex align-items-center mt-0 ">
                                                <div class="h-avatar is-medium h-5">
                                                    <img class="avatar rounded-circle" alt="user-icon" src="{{ getSingleMedia($provider,'profile_image', null) }}">
                                                </div>
                                                <div class="data-content">
                                                    <div>
                                                        <span class="font-weight-bold">{{!empty($provider->display_name) ? $provider->display_name : '-'}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0  d-flex justify-content-start align-items-center">
                                            {{!empty($provider->email) ? $provider->email : '-'}}
                                            </p>
                                        </td>
                                        <td>{{!empty($provider->contact_number) ? $provider->contact_number : '-'}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->Upcoming_Booking_card)) || $show == "true")
            <div class="col-md-6">
                <div class="card card-block card-height">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="font-weight-bold">{{__('messages.dashboard_upcomming_booking')}}</h5>
                        <a href="" class="float-right mr-1 btn btn-sm btn-primary">{{ __('messages.see_all') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive max-height-400">
                            <table class="table table-spacing mb-0">
                                <thead class="table-color-heading">
                                <tr class="text-secondary">
                                    <th scope="col">{{__('messages.date')}}</th>
                                    <th scope="col">{{__('messages.service')}}</th>
                                    <th scope="col">{{__('messages.user')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if(count($data['dashboard']['upcomming_booking']) > 0)
                                        @foreach($data['dashboard']['upcomming_booking'] as $booking)
                                            <tr class="white-space-no-wrap">
                                                <td>
                                                    {{date("d M Y", strtotime($booking->date))}}
                                                </td>
                                                <td class="pl-0 py-3 ">
                                                    {{optional($booking->service)->name ?? '-'}}
                                                </td>
                                                <td class="pl-0 py-3 font-weight-bold">
                                                    {{optional($booking->customer)->display_name ?? '-'}}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                    <div class="data-not-found">{{__('messages.not_found_entry',['name' => __('messages.booking')] )}}</div>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
            @if(($data['dashboard_setting'] != [] && !empty($data['dashboard_setting']->New_Customer_card)) || $show == "true")
            <div class="col-md-6">
                <div class="card">
                    <div class="d-flex justify-content-between align-items-center p-3">
                        <h5 class="font-weight-bold">{{ __('messages.new_customer') }}</h4>
                        <a href="{{ route('user.index') }}" class="float-right mr-1 btn btn-sm btn-primary">{{ __('messages.see_all') }}</a>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive max-height-400">
                            <table class="table mb-0">
                                <thead class="table-color-heading">
                                <tr class="text-secondary">
                                    <th scope="col">{{__('messages.date')}}</th>
                                    <th scope="col">{{__('messages.user')}}</th>
                                    <th scope="col">{{__('messages.email')}}</th>
                                    <th scope="col" class="white-space-no-wrap">{{__('messages.contact_number')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($data['dashboard']['new_customer'] as $customer)
                                    <tr class="white-space-no-wrap">
                                        <td> {{date("d M Y", strtotime($customer->created_at))}}</td>
                                        <td>
                                            <div class="active-project-1 d-flex align-items-center mt-0 ">
                                                <div class="h-avatar is-medium h-5">
                                                    <img class="avatar rounded-circle" alt="user-icon" src="{{ getSingleMedia($customer,'profile_image', null) }}">
                                                </div>
                                                <div class="data-content">
                                                    <div>
                                                        <span class="font-weight-bold">{{!empty($customer->display_name) ? $customer->display_name : '-'}}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="mb-0  d-flex justify-content-start align-items-center">
                                               {{!empty($customer->email) ? $customer->email : '-'}}
                                            </p>
                                        </td>
                                        <td>{{!empty($customer->contact_number) ? $customer->contact_number : '-'}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-master-layout>
<script>
    var chartData = '<?php echo $data['category_chart']['chartdata']; ?>';
    var chartArray = JSON.parse(chartData);
    var chartlabel = '<?php echo $data['category_chart']['chartlabel']; ?>';
    var labelsArray = JSON.parse(chartlabel);
    if(jQuery('#monthly-revenue').length){
        var options = {
        series: [{
            name: 'revenue',
            data: [ {{ implode ( ',' ,$data['revenueData'] ) }} ]
        }],
        chart: {
          height: 265,
          type: 'bar',
          toolbar:{
            show: true,
          },
          events: {
            click: function(chart, w, e) {
            }
          }
        },
        plotOptions: {
            bar: {
                horizontal: false,
                s̶t̶a̶r̶t̶i̶n̶g̶S̶h̶a̶p̶e̶: 'flat',
                e̶n̶d̶i̶n̶g̶S̶h̶a̶p̶e̶: 'flat',
                borderRadius: 0,
                columnWidth: '70%',
                barHeight: '70%',
                distributed: false,
                rangeBarOverlap: true,
                rangeBarGroupRows: false,
                colors: {
                    ranges: [{
                        from: 0,
                        to: 0,
                        color: undefined
                    }],
                    backgroundBarColors: [],
                    backgroundBarOpacity: 1,
                    backgroundBarRadius: 0,
                },
                dataLabels: {
                    position: 'top',
                    maxItems: 100,
                    hideOverflowingLabels: true,
                }
            }
        },
        dataLabels: {
          enabled: false
        },
        grid: {
          xaxis: {
              lines: {
                  show: false
              }
          },
          yaxis: {
              lines: {
                  show: true
              }
          }
        },
        legend: {
          show: false
        },
        yaxis: {
          labels: {
          offsetY:0,
          minWidth: 20,
          maxWidth: 20
          },
        },
        xaxis: {
          categories: [
            'Jan',
            'Feb',
            'Mar',
            'Apr',
            'May',
            'June',
            'Jul',
            'Aug',
            'Sep',
            'Oct',
            'Nov',
            'Dec'
          ],
          labels: {
            minHeight: 22,
            maxHeight: 22,
            style: {
              fontSize: '12px'
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#monthly-revenue"), options);
        chart.render();
    }

</script>
