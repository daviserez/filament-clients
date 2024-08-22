<li>
    <form class="my-2 grid grid-cols-[auto_1fr] items-center gap-3 rounded-md border border-slate-200 p-2 dark:border-slate-500"
          wire:submit="remove">
        <x-filament::icon-button color="danger"
                                 icon="heroicon-s-x-mark"
                                 type="submit" />
        <span>{{ $teamMember->name }} ({{ $teamMember->email }})</span>
    </form>
</li>
