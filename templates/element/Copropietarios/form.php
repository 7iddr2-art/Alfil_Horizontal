<fieldset><legend>Identificación</legend>
<?= $this->Form->control('document_type', ['label' => 'Tipo de documento', 'options' => ['CC' => 'Cédula de ciudadanía', 'CE' => 'Cédula de extranjería', 'NIT' => 'NIT', 'PP' => 'Pasaporte'], 'empty' => 'Seleccione']) ?>
<?= $this->Form->control('document_number', ['label' => 'Número de documento']) ?>
<?= $this->Form->control('first_name', ['label' => 'Primer nombre']) ?>
<?= $this->Form->control('middle_name', ['label' => 'Segundo nombre']) ?>
<?= $this->Form->control('last_name', ['label' => 'Primer apellido']) ?>
<?= $this->Form->control('second_last_name', ['label' => 'Segundo apellido']) ?></fieldset>
<fieldset><legend>Datos demográficos y contacto</legend>
<?= $this->Form->control('birth_date', ['label' => 'Fecha de nacimiento', 'type' => 'date', 'empty' => true]) ?>
<?= $this->Form->control('gender', ['label' => 'Género', 'options' => ['female' => 'Femenino', 'male' => 'Masculino', 'non_binary' => 'No binario', 'prefer_not_to_say' => 'Prefiero no indicarlo'], 'empty' => 'Seleccione']) ?>
<?= $this->Form->control('nationality', ['label' => 'Nacionalidad']) ?>
<?= $this->Form->control('marital_status', ['label' => 'Estado civil', 'options' => ['single' => 'Soltero(a)', 'married' => 'Casado(a)', 'domestic_partnership' => 'Unión marital de hecho', 'divorced' => 'Divorciado(a)', 'widowed' => 'Viudo(a)'], 'empty' => 'Seleccione']) ?>
<?= $this->Form->control('email', ['label' => 'Correo electrónico']) ?>
<?= $this->Form->control('mobile_phone', ['label' => 'Celular']) ?>
<?= $this->Form->control('landline_phone', ['label' => 'Teléfono fijo']) ?>
<?= $this->Form->control('correspondence_address', ['label' => 'Dirección de correspondencia']) ?></fieldset>
<fieldset><legend>Contacto de emergencia</legend>
<?= $this->Form->control('emergency_contact_name', ['label' => 'Nombre']) ?>
<?= $this->Form->control('emergency_contact_phone', ['label' => 'Teléfono']) ?>
<?= $this->Form->control('active', ['label' => 'Registro activo']) ?></fieldset>
