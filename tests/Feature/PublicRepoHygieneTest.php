<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicRepoHygieneTest extends TestCase
{
    public function test_deploy_script_has_no_hardcoded_ssh_target(): void
    {
        $script = (string) file_get_contents(base_path('scripts/hostoo-sync-build.sh'));

        $this->assertStringContainsString('scripts/hostoo.env', $script);
        $this->assertDoesNotMatchRegularExpression('/@ssh\.[a-z0-9.-]+/i', $script);
        $this->assertDoesNotMatchRegularExpression('/HOSTOO_SSH_PORT=\$\{HOSTOO_SSH_PORT:-\d+\}/', $script);
    }

    public function test_public_docs_do_not_embed_ssh_scp_host(): void
    {
        $doc = (string) file_get_contents(base_path('docs/deploy/hostoo-git-ssh.md'));

        $this->assertStringContainsString('hostoo.env', $doc);
        $this->assertDoesNotMatchRegularExpression('/scp -P \d+/', $doc);
    }
}
