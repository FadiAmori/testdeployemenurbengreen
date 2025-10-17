@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <span style="font-family: 'Dosis', sans-serif; font-weight: 700; letter-spacing: 1px; color: #fff; font-size: 28px;">
                <span style="color: #70c745;">URBAN</span>GREEN
            </span>
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @isset($subcopy)
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} UrbanGreen. All rights reserved.
        @endcomponent
    @endslot
@endcomponent