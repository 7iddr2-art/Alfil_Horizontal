<?php
declare(strict_types=1);

namespace App\Service;

use Cake\Http\Exception\ForbiddenException;
use Cake\Http\ServerRequest;

final class TenantContextSession
{
    private const SESSION_KEY = 'TenantContext';

    /** Receives the resolver used to revalidate each session selection. */
    public function __construct(
        private readonly TenantContextResolver $resolver,
    ) {
    }

    /** Applies a session selection only after revalidating its identity and membership. */
    public function apply(ServerRequest $request, int $userId): ServerRequest
    {
        $selection = $request->getSession()->read(self::SESSION_KEY);
        if ($selection === null) {
            return $request;
        }

        if (!is_array($selection) || !isset($selection['user_id'], $selection['tenant_id'])) {
            $this->clear($request);

            throw new ForbiddenException('Tenant context is not available.');
        }

        $selectedUserId = filter_var($selection['user_id'], FILTER_VALIDATE_INT);
        if ($selectedUserId === false || $selectedUserId < 1) {
            $this->clear($request);

            throw new ForbiddenException('Tenant context is not available.');
        }

        if ($selectedUserId !== $userId) {
            $this->clear($request);

            return $request;
        }

        $tenantId = filter_var($selection['tenant_id'], FILTER_VALIDATE_INT);
        if ($tenantId === false || $tenantId < 1) {
            $this->clear($request);

            throw new ForbiddenException('Tenant context is not available.');
        }

        try {
            $context = $this->resolver->resolve($userId, $tenantId);
        } catch (ForbiddenException $exception) {
            $this->clear($request);

            throw $exception;
        }

        return $request->withAttribute('tenantContext', $context);
    }

    /** Stores a validated selection bound to the authenticated user. */
    public function select(ServerRequest $request, TenantContext $context): void
    {
        $request->getSession()->write(self::SESSION_KEY, [
            'user_id' => $context->userId,
            'tenant_id' => $context->tenantId,
        ]);
    }

    /** Removes the current tenant selection from the server-side session. */
    public function clear(ServerRequest $request): void
    {
        $request->getSession()->delete(self::SESSION_KEY);
    }
}
