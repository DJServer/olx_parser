<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    Подписка на изменение цены
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="subscribe">
                        <div class="mb-3">
                            <label for="url" class="form-label">Ссылка на OLX объявление</label>
                            <input type="url" class="form-control" id="url" wire:model="url">
                            @error('url') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Подписаться</button>
                    </form>

                    <div wire:loading class="mt-3 text-info">⏳ Подписка обрабатывается...</div>

                    <div id="subscription-message" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    Livewire.on('subscription:success', message => {
        document.getElementById('subscription-message').innerHTML = `<div class="alert alert-success">${message}</div>`;
    });

    Livewire.on('subscription:error', message => {
        document.getElementById('subscription-message').innerHTML = `<div class="alert alert-danger">${message}</div>`;
    });
</script>
