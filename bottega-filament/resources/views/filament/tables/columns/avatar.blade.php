@php($client = $getRecord())

<div class="flex h-10 w-10 items-center justify-center rounded-full uppercase text-white"
    style="background-color: {{ $client->avatar_color }}">
    <div>{{ $client->firstname[0] ?? '' }}{{ $client->name[0] }}</div>
</div>
