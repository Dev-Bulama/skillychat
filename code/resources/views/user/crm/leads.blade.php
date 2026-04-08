@extends('layouts.master')
@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="i-card-md">
            <div class="card--header">
                <h4 class="card-title"><i class="bi bi-people me-2" style="color:#6d28d9;"></i>{{ translate('All Leads') }}</h4>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('user.crm.index') }}" class="i-btn btn--sm btn--outline"><i class="bi bi-kanban me-1"></i>{{ translate('Kanban') }}</a>
                    <a href="{{ route('user.crm.create') }}" class="i-btn btn--sm btn--primary"><i class="bi bi-plus-circle me-1"></i>{{ translate('Add Lead') }}</a>
                </div>
            </div>
            <div class="card-body">
                <form method="get" class="row g-2 mb-4">
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="{{ translate('Search name, email, phone…') }}" value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="stage_id" class="form-select form-select-sm">
                            <option value="">{{ translate('All Stages') }}</option>
                            @foreach($stages as $stage)
                            <option value="{{ $stage->id }}" {{ request('stage_id') == $stage->id ? 'selected' : '' }}>{{ $stage->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">{{ translate('All Status') }}</option>
                            @foreach(['active','won','lost','archived'] as $s)
                            <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="i-btn btn--sm btn--outline w-100">{{ translate('Filter') }}</button>
                    </div>
                </form>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle fs-14">
                        <thead>
                            <tr>
                                <th>{{ translate('Name') }}</th>
                                <th>{{ translate('Company') }}</th>
                                <th>{{ translate('Contact') }}</th>
                                <th>{{ translate('Stage') }}</th>
                                <th>{{ translate('Deal') }}</th>
                                <th>{{ translate('Priority') }}</th>
                                <th>{{ translate('Added') }}</th>
                                <th>{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($leads as $lead)
                            <tr>
                                <td><a href="{{ route('user.crm.show', $lead->uid) }}" class="fw-semibold text-decoration-none">{{ $lead->name }}</a></td>
                                <td>{{ $lead->company ?: '—' }}</td>
                                <td>
                                    @if($lead->email)<div class="fs-12 text-truncate" style="max-width:140px;">{{ $lead->email }}</div>@endif
                                    @if($lead->phone)<div class="fs-12">{{ $lead->phone }}</div>@endif
                                </td>
                                <td><span class="badge" style="background:{{ $lead->stage->color }};color:#fff;">{{ $lead->stage->name }}</span></td>
                                <td>{{ $lead->deal_value > 0 ? '$'.number_format($lead->deal_value,0) : '—' }}</td>
                                <td>
                                    <span class="badge bg-{{ $lead->priority === 'high' ? 'danger' : ($lead->priority === 'medium' ? 'warning text-dark' : 'secondary') }}">
                                        {{ ucfirst($lead->priority) }}
                                    </span>
                                </td>
                                <td>{{ $lead->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('user.crm.show', $lead->uid) }}" class="i-btn btn--sm btn--primary"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('user.crm.edit', $lead->uid) }}" class="i-btn btn--sm btn--outline"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('user.crm.destroy', $lead->uid) }}" method="post" onsubmit="return confirm('Delete this lead?')">
                                            @csrf @method('DELETE')
                                            <button class="i-btn btn--sm btn--danger"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="8" class="text-center py-4">@include('admin.partials.not_found')</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $leads->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
