@component('mail::message')
# Congelamiento de Indicadores
 
El proceso de congelamiento de indicadores correspondiente al periodo **{{ $date }}** se ha realizado con éxito! 

**Fecha y Hora de Inicio:** {{ $start_time }}

**Fecha y Hora de Finalización:** {{ $finish_time }}

@component('mail::button', ['url' => 'https://udicat.muniguate.com/apps/dashboard-iso/#/'])
Ver Indicadores
@endcomponent
 
@endcomponent