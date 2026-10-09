<div class="content admin-page">
    <h1>Asignación de parqueadero</h1>
    <?= $this->Form->create($item) ?>
    <?= $this->Form->control('parqueadero_id', ['options' => $parqueaderos, 'label' => 'Parqueadero']) ?>
    <?= $this->Form->hidden('residente_id', ['id' => 'residente-id']) ?>
    <label for="residente-search">Residente</label>
    <input id="residente-search" type="search" autocomplete="off" placeholder="Documento, nombres o apellidos">
    <small>Escriba mínimo dos caracteres y seleccione el residente.</small>
    <div id="residente-results" role="listbox"></div>
    <?= $this->Form->hidden('vivienda_id', ['id' => 'vivienda-id']) ?>
    <label for="vivienda-search">Apartamento o casa donde reside</label>
    <input id="vivienda-search" type="search" autocomplete="off" required value="<?= h($viviendaCode) ?>" placeholder="Código de vivienda">
    <small>Busque por código y seleccione la vivienda.</small>
    <div id="vivienda-results" role="listbox" aria-live="polite"></div>
    <?= $this->Form->control('vehicle_plate', ['label' => 'Placa']) ?>
    <?= $this->Form->control('vehicle_description', ['label' => 'Vehículo']) ?>
    <?= $this->Form->control('monthly_fee', ['label' => 'Mensualidad', 'type' => 'number', 'step' => '0.01']) ?>
    <?= $this->Form->control('start_date', ['label' => 'Inicio', 'type' => 'date']) ?>
    <?= $this->Form->control('end_date', ['label' => 'Finalización', 'type' => 'date']) ?>
    <?= $this->Form->control('active', ['label' => 'Asignación activa']) ?>
    <?= $this->Form->control('notes', ['label' => 'Observaciones']) ?>
    <?= $this->Form->button('Guardar') ?>
    <?= $this->Form->end() ?>
</div>
<script>
(() => {
    const input = document.getElementById('residente-search');
    const hidden = document.getElementById('residente-id');
    const results = document.getElementById('residente-results');
    let request = 0;

    input.addEventListener('input', async () => {
        const current = ++request;
        const query = input.value.trim();
        hidden.value = '';
        results.innerHTML = '';
        if (query.length < 2) return;

        const residents = await fetch('<?= $this->Url->build('/residentes/search') ?>?q=' + encodeURIComponent(query)).then(response => response.json());
        if (current !== request) return;
        [...new Map(residents.map(resident => [resident.id, resident])).values()].forEach(resident => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = resident.label;
            button.onclick = () => {
                hidden.value = resident.id;
                input.value = resident.label;
                results.innerHTML = '';
            };
            results.appendChild(button);
        });
    });

    const homeInput = document.getElementById('vivienda-search');
    const homeId = document.getElementById('vivienda-id');
    const homeResults = document.getElementById('vivienda-results');
    const form = homeInput.closest('form');
    let homeRequest = 0;

    homeInput.addEventListener('input', async () => {
        const current = ++homeRequest;
        const query = homeInput.value.trim();
        homeId.value = '';
        homeInput.setCustomValidity('');
        homeResults.innerHTML = '';
        if (!query) return;

        try {
            const response = await fetch('<?= $this->Url->build('/viviendas/search') ?>?q=' + encodeURIComponent(query));
            if (!response.ok) throw new Error('La búsqueda de viviendas falló.');
            const homes = await response.json();
            if (current !== homeRequest) return;
            if (homes.length === 0) {
                homeResults.textContent = 'No se encontraron viviendas con ese código.';
                return;
            }
            homes.forEach(home => {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = home.label;
                button.onclick = () => {
                    homeId.value = home.id;
                    homeInput.value = home.label;
                    homeInput.setCustomValidity('');
                    homeResults.innerHTML = '';
                };
                homeResults.appendChild(button);
            });
        } catch (error) {
            if (current !== homeRequest) return;
            homeResults.textContent = 'No se pudo buscar la vivienda. Intente de nuevo.';
        }
    });

    form.addEventListener('submit', event => {
        if (homeId.value) return;
        event.preventDefault();
        homeInput.setCustomValidity('Seleccione una vivienda de los resultados.');
        homeInput.reportValidity();
    });
})();
</script>
