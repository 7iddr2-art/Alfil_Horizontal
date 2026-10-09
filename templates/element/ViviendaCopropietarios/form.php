<fieldset>
    <legend>Relación de propiedad</legend>
    <?= $this->Form->control('vivienda_id', ['label' => 'Vivienda', 'options' => $viviendas, 'empty' => 'Seleccione']) ?>
    <?= $this->Form->hidden('copropietario_id', ['id' => 'copropietario-id']) ?>
    <div class="input text">
        <label for="copropietario-search">Copropietario</label>
        <input id="copropietario-search" type="search" autocomplete="off" placeholder="Documento, nombres o apellidos">
        <small>Escriba al menos dos caracteres y seleccione un resultado.</small>
        <div id="copropietario-results" role="listbox"></div>
    </div>
    <?= $this->Form->control('relationship_type', ['label' => 'Calidad', 'options' => ['owner' => 'Propietario', 'usufructuary' => 'Usufructuario', 'legal_representative' => 'Representante legal']]) ?>
    <?= $this->Form->control('ownership_percentage', ['label' => 'Participación en la vivienda (%)', 'type' => 'number', 'step' => '0.0001', 'min' => 0, 'max' => 100]) ?>
    <?= $this->Form->control('start_date', ['label' => 'Fecha de inicio', 'type' => 'date', 'empty' => true]) ?>
    <?= $this->Form->control('end_date', ['label' => 'Fecha de finalización', 'type' => 'date', 'empty' => true]) ?>
    <?= $this->Form->control('active', ['label' => 'Relación activa']) ?>
    <?= $this->Form->control('notes', ['label' => 'Observaciones']) ?>
</fieldset>
<script>
(() => {
    const input = document.getElementById('copropietario-search');
    const hidden = document.getElementById('copropietario-id');
    const results = document.getElementById('copropietario-results');
    let requestNumber = 0;
    input.addEventListener('input', async () => {
        const currentRequest = ++requestNumber;
        const query = input.value.trim();
        hidden.value = '';
        results.innerHTML = '';
        if (query.length < 2) return;
        const response = await fetch('<?= $this->Url->build('/copropietarios/search') ?>?q=' + encodeURIComponent(query));
        const owners = await response.json();
        if (currentRequest !== requestNumber) return;
        const uniqueOwners = [...new Map(owners.map((owner) => [owner.id, owner])).values()];
        uniqueOwners.forEach((owner) => {
            const option = document.createElement('button');
            option.type = 'button';
            option.textContent = owner.label;
            option.addEventListener('click', () => {
                hidden.value = owner.id;
                input.value = owner.label;
                results.innerHTML = '';
            });
            results.appendChild(option);
        });
    });
})();
</script>
