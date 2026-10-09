<fieldset><legend>Identificación y ubicación</legend>
<?= $this->Form->control('code', ['label' => 'Código interno']) ?>
<?= $this->Form->control('type', ['label' => 'Tipo de vivienda', 'options' => ['apartment' => 'Apartamento', 'house' => 'Casa'], 'empty' => 'Seleccione']) ?>
<?= $this->Form->control('tower_block', ['label' => 'Torre o bloque']) ?>
<?= $this->Form->control('number', ['label' => 'Número de vivienda']) ?>
<?= $this->Form->control('floor', ['label' => 'Piso', 'type' => 'number']) ?></fieldset>
<fieldset><legend>Características</legend>
<?= $this->Form->control('area_m2', ['label' => 'Área privada (m²)', 'type' => 'number', 'step' => '0.01']) ?>
<?= $this->Form->control('coownership_percentage', ['label' => 'Coeficiente de copropiedad (%)', 'type' => 'number', 'step' => '0.0001']) ?>
<?= $this->Form->control('bedrooms', ['label' => 'Habitaciones', 'type' => 'number', 'min' => 0]) ?>
<?= $this->Form->control('bathrooms', ['label' => 'Baños', 'type' => 'number', 'min' => 0]) ?>
<?= $this->Form->control('status', ['label' => 'Estado', 'options' => ['active' => 'Activa', 'inactive' => 'Inactiva']]) ?>
<?= $this->Form->control('notes', ['label' => 'Observaciones']) ?></fieldset>
