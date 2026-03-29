@extends('admin.layouts.master')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title">
                    <i class="las la-toggle-on me-1"></i> {{translate('Feature Flags')}}
                </h4>
                <p class="text-muted mb-0">{{translate('Enable or disable platform features globally for all users.')}}</p>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($flags as $flag)
                    <div class="col-md-6 col-xl-4">
                        <div class="card border h-100">
                            <div class="card-body d-flex flex-column gap-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1 fw-semibold">{{$flag->label}}</h6>
                                        <small class="text-muted">{{$flag->description}}</small>
                                    </div>
                                    <span class="badge {{$flag->is_enabled ? 'bg-success' : 'bg-secondary'}} ms-2 text-nowrap">
                                        {{$flag->is_enabled ? translate('Enabled') : translate('Disabled')}}
                                    </span>
                                </div>
                                <div class="mt-auto pt-2 d-flex gap-2 align-items-center">
                                    <form action="{{route('admin.feature-flags.toggle')}}" method="POST">
                                        @csrf
                                        <input type="hidden" name="id" value="{{$flag->id}}">
                                        <button type="submit" class="i-btn {{$flag->is_enabled ? 'danger' : 'success'}} btn--sm">
                                            <i class="las la-{{$flag->is_enabled ? 'toggle-off' : 'toggle-on'}} me-1"></i>
                                            {{$flag->is_enabled ? translate('Disable') : translate('Enable')}}
                                        </button>
                                    </form>
                                    <small class="text-muted">
                                        <code>{{$flag->name}}</code>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                @if($flags->isEmpty())
                <div class="text-center py-4 text-muted">
                    <i class="las la-toggle-on fs-1"></i>
                    <p>{{translate('No feature flags found.')}}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
