<div class="container mt-4">
    <h3>Ваши подписки</h3>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Название</th>
            <th>Последнее обновление</th>
            <th>Последняя цена</th>
            <th>История</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($announcements as $announcement)
            <tr>
                <td>
                    <a href="{{ $announcement->url }}" target="_blank">{{ $announcement->title }}</a>
                </td>
                <td>{{ $announcement->last_refresh_time ?? '—' }}</td>
                <td>
                    @if($announcement->prices->isNotEmpty())
                        {{ $announcement->prices->first()->price }} {{ $announcement->prices->first()->currency }}
                    @else
                        —
                    @endif
                </td>
                <td>
                    <button wire:click="togglePriceHistory({{ $announcement->id }})" class="btn btn-info btn-sm">
                        История
                    </button>
                </td>
                <td>
                    <button wire:click="confirmUnsubscribe({{ $announcement->id }})" class="btn btn-danger btn-sm">
                        Удалить
                    </button>
                </td>
            </tr>

            @if(isset($expandedAnnouncements[$announcement->id]))
                <tr>
                    <td colspan="5">
                        <h5>История цен</h5>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Цена</th>
                                <th>Валюта</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($announcement->prices()->orderBy('created_at', 'desc')->get() as $price)
                                <tr>
                                    <td>{{ $price->created_at }}</td>
                                    <td>{{ $price->price }}</td>
                                    <td>{{ $price->currency }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </td>
                </tr>
            @endif
        @endforeach
        </tbody>
    </table>

    @if($confirmingUnsubscribe)
        <div class="modal d-block" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Подтверждение удаления</h5>
                        <button type="button" class="close" wire:click="$set('confirmingUnsubscribe', null)" aria-label="Закрыть">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Вы уверены, что хотите удалить подписку?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="$set('confirmingUnsubscribe', null)">Отмена</button>
                        <button type="button" class="btn btn-danger" wire:click="unsubscribe">Удалить</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
