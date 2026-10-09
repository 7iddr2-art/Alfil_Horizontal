<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\TenantMembership> $availableTenants
 * @var \App\Service\TenantContext|null $tenantContext
 */
?>
<section class="tenant-context">
    <h1><?= h(__('Seleccionar tenant')) ?></h1>

    <?php if ($tenantContext !== null) : ?>
        <p><?= h(__('Tenant activo: {0}', $tenantContext->tenantId)) ?></p>
    <?php endif; ?>

    <?php if (count($availableTenants) === 0) : ?>
        <p><?= h(__('No tiene tenants activos disponibles.')) ?></p>
    <?php else : ?>
        <?php
        $tenantOptions = [];
        foreach ($availableTenants as $membership) {
            $tenantOptions[$membership->tenant_id] = $membership->tenant->name;
        }
        ?>
        <?= $this->Form->create(null, ['url' => ['action' => 'select']]) ?>
        <?= $this->Form->control('tenant_id', [
            'label' => __('Tenant'),
            'options' => $tenantOptions,
            'value' => $tenantContext?->tenantId,
            'empty' => __('Seleccione un tenant'),
        ]) ?>
        <?= $this->Form->button(__('Usar tenant')) ?>
        <?= $this->Form->end() ?>
    <?php endif; ?>
</section>
