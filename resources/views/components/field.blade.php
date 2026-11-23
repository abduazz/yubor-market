@props([
    'label', 'name', 'placeholder' => '', 'type' => 'text', 'value' => null, 'inputmode' => null,
    'required' => false, 'rule' => null, 'message' => null, 'maxlength' => null,
    'multiline' => false, // true — pastga cho'ziladigan ko'p qatorli maydon (textarea)
    'prefix' => null,     // masalan '+7' yoki '+998' — o'chirib bo'lmaydigan doimiy boshlanma
])

{{-- Figma: Text input / Input container (h 58, bg #f7f7f8, radius 16).
     Holatlar:
       bo'sh      — label #525252 regular, placeholder #a3a3a3
       fokus      — ko'k chegara (primary)
       to'ldirilgan — label #737373 medium, qiymat #444 medium
       xato       — qizil chegara + och qizil fon, label qizil, ostida xato matni
     Validatsiya: resources/js/app.js → initCheckout() (data-rule, data-required) --}}
@php
    $control = 'w-full min-w-0 bg-transparent p-0 text-[16px] leading-[24px] text-[#444] outline-none placeholder:text-muted not-placeholder-shown:font-medium';
    $filled = 'group-has-[input:not(:placeholder-shown)]/field:font-medium group-has-[input:not(:placeholder-shown)]/field:text-ink-3 group-has-[textarea:not(:placeholder-shown)]/field:font-medium group-has-[textarea:not(:placeholder-shown)]/field:text-ink-3';
@endphp

<div class="group/field flex w-full flex-col gap-[6px]" data-field data-invalid="false">
    <label for="{{ $name }}"
           class="flex w-full cursor-text overflow-clip rounded-[16px] border border-transparent bg-field px-[14px] transition-colors
                  {{ $multiline ? 'min-h-[88px] items-start py-[10px]' : 'h-[58px] items-center py-[8px]' }}
                  focus-within:border-primary
                  group-data-[invalid=true]/field:!border-[#f04438] group-data-[invalid=true]/field:bg-[#fef3f2]">
        <span class="flex min-w-0 flex-1 flex-col justify-center gap-[2px]">
            <span class="text-[12px] leading-[16px] text-ink-2 {{ $filled }} group-data-[invalid=true]/field:!text-[#d92d20]">
                {{ $label }}
            </span>

            @if ($multiline)
                <textarea name="{{ $name }}" id="{{ $name }}" rows="2" data-autogrow
                          placeholder="{{ $placeholder }}"
                          @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                          @if ($required) data-required @endif
                          @if ($rule) data-rule="{{ $rule }}" @endif
                          @if ($message) data-message="{{ $message }}" @endif
                          aria-describedby="{{ $name }}-error"
                          {{ $attributes->class($control.' resize-none overflow-hidden break-words') }}>{{ $value }}</textarea>
            @else
                <span class="flex w-full items-center">
                    @if ($prefix)
                        <span class="mr-[5px] shrink-0 text-[16px] leading-[24px] font-medium text-[#444] select-none" data-prefix>{{ $prefix }}</span>
                    @endif
                    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ $value }}"
                           placeholder="{{ $placeholder }}"
                           @if ($inputmode) inputmode="{{ $inputmode }}" @endif
                           @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                           @if ($required) data-required @endif
                           @if ($rule) data-rule="{{ $rule }}" @endif
                           @if ($message) data-message="{{ $message }}" @endif
                           @if ($prefix) data-prefix-value="{{ $prefix }}" @endif
                           aria-describedby="{{ $name }}-error"
                           {{ $attributes->class($control) }}>
                </span>
            @endif
        </span>
    </label>
    <p id="{{ $name }}-error" data-error role="alert"
       class="hidden items-center gap-[4px] px-[4px] text-[12px] leading-[16px] text-[#d92d20] group-data-[invalid=true]/field:flex"></p>
</div>
