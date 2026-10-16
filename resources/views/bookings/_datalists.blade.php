@foreach(['flight','hotel','transport','visa'] as $t)
    <datalist id="dl-{{ $t }}">@foreach(($suppliers[$t] ?? collect()) as $s)<option value="{{ $s->name }}">@endforeach</datalist>
@endforeach
<datalist id="dl-city"><option value="Makkah"><option value="Madinah"><option value="Jeddah"><option value="Taif"></datalist>
<datalist id="dl-room"><option value="Double"><option value="Triple"><option value="Quad"><option value="Quint"><option value="Suite"></datalist>
<datalist id="dl-meal"><option value="Room only"><option value="Breakfast"><option value="Half board"><option value="Full board"></datalist>
<datalist id="dl-view"><option value="Standard"><option value="City view"><option value="Haram view"><option value="Kaaba view"></datalist>
<datalist id="dl-vehicle"><option value="Car (4 seats)"><option value="GMC Yukon"><option value="H1 Van"><option value="Hiace"><option value="Coaster"><option value="Bus"></datalist>
<datalist id="dl-visa-type"><option value="Umrah visa"><option value="Tourist visa"><option value="Transit visa"></datalist>
