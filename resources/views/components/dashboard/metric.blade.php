@props(['title', 'value','icon' => 'bi-info-circle', 'bg' => 'primary','href'])
@if ($href == '#')
    @php $href = 'user.dashboard'; @endphp
@endif
<a href="{{ route($href) }}" style="text-decoration: none !important;">
<div class="card text-white bg-{{ $bg }} shadow-sm rounded-3 h-100">
    <div class="card-body d-flex flex-column justify-content-between">
        <div class="d-flex align-items-center mb-3">
            <i class="bi {{ $icon }} fs-2 me-3"></i>
            <h6 class="mb-0 fw-semibold w-100 text-truncate" style="font-size: clamp(0.85rem, 1.2vw, 1rem);">
                {{ $title }}
            </h6>
        </div>
        <h3 class="price fw-bold mb-0">{{ $value }}</h3>
    </div>
</div>
</a>

