@extends('layouts.admin')
@section('content')
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex">
            <h6 class="m-0 font-weight-bold text-primary">Product Coupons</h6>
            <div class="ml-auto">
{{--                @ability('admin', 'create_product_categories')--}}
                <a href="{{ route('admin.product_coupons.create') }}" class="btn btn-primary">
                    <span class="icon text-white-50">
                        <i class="fa fa-plus"></i>
                    </span>
                    <span class="text">Add new coupon</span>
                </a>
{{--                @endability--}}
            </div>
        </div>


        @include('backend.product_coupons.filter.filter')
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Value</th>
                        <th>Status</th>
                        <th>Use times</th>
                        <th>Validity date</th>
                        <th>Greater then</th>
                        <th>Created at</th>
                        <th class="text-center" style="width: 30px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($coupons as $coupon)
                    <tr>
                        <td>{{ $coupon->code }}</td>
                        <td>{{ $coupon->value }} {{ $coupon->type == 'fixed' ? '$' : '%' }}</td>
                        <td>{{ $coupon->status() }}</td>
                        <td>{{ $coupon->used_times . '/' . $coupon->use_times }}</td>
                        <td>{{ $coupon->staer_date != '' ? $coupon->start_date->format('y-m-d'). ' - ' . $coupon->expire_date->format('y-m-d') : ' - ' }}</td>
                        <td>{{ $coupon->greater_than ?? '-' }}</td>
                        <td>{{ $coupon->created_at->format('y-m-d h:i a') }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.product_coupons.edit', $coupon->id) }}" class="btn btn-primary">
                                    <i class="fa fa-edit"></i>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-danger"
                                   onclick="if (confirm('Are yuo sure to delete this record ?')){document.getElementById('delete-product-coupon-{{ $coupon->id }}').submit();} else {return false;}">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </div>
                            <form action="{{ route('admin.product_coupons.destroy', $coupon->id) }}" method="post" id="delete-product-coupon-{{ $coupon->id }}" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                    <td colspan="8" class="text-center">No coupons found</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="8">
                            <div class="float-right">
                                {!! $coupons->appends(request()->all())->links() !!}
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>


    </div>
@endsection
