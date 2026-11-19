@props(['op'])
@if (! empty($op['operator_name']))
    <strong>{{ $op['operator_name'] }}</strong>, {{ $op['operator_address'] }}@if (! empty($op['operator_nip'])), NIP {{ $op['operator_nip'] }}@endif @if (! empty($op['operator_regon'])), REGON {{ $op['operator_regon'] }}@endif, e-mail: <a href="mailto:{{ $op['operator_email'] }}" class="text-k19-red">{{ $op['operator_email'] }}</a>
@else
    {{ app()->getLocale() === 'pl' ? 'operator portalu kanal19.pl (pełne dane rejestrowe zostaną tu wkrótce uzupełnione), e-mail:' : 'the operator of kanal19.pl (full registration details will be added here shortly), e-mail:' }} <a href="mailto:admin@kanal19.pl" class="text-k19-red">admin@kanal19.pl</a>
@endif
