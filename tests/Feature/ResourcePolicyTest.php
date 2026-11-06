<?php

namespace Javaabu\Permissions\Tests\Feature;

use Illuminate\Support\Facades\Gate;
use Javaabu\Permissions\Models\Permission;
use Javaabu\Permissions\Tests\InteractsWithDatabase;
use Javaabu\Permissions\Tests\Models\Admin;
use Javaabu\Permissions\Tests\Models\Post;
use Javaabu\Permissions\Tests\Models\User;
use Javaabu\Permissions\Tests\Policies\AdminPostPolicy;
use Javaabu\Permissions\Tests\Policies\OwnedPostPolicy;
use Javaabu\Permissions\Tests\Policies\PostPolicy;
use Javaabu\Permissions\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ResourcePolicyTest extends TestCase
{
    use InteractsWithDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view', 'edit', 'edit_others', 'delete', 'delete_others', 'force_delete', 'publish', 'approve'] as $action) {
            Permission::query()->create(['name' => "{$action}_posts", 'guard_name' => 'web']);
            Permission::query()->create(['name' => "{$action}_posts", 'guard_name' => 'web_admin']);
        }
    }

    #[Test]
    public function it_maps_the_standard_abilities_to_permissions(): void
    {
        Gate::policy(Post::class, PostPolicy::class);

        $post = $this->createPost();

        $this->assertAbilities($this->createUser(), $post, []);
        $this->assertAbilities($this->createUser(['view_posts']), $post, ['viewAny', 'view']);
        $this->assertAbilities($this->createUser(['edit_posts']), $post, ['create', 'update']);
        $this->assertAbilities($this->createUser(['delete_posts']), $post, ['delete', 'restore', 'viewTrash']);
        $this->assertAbilities($this->createUser(['force_delete_posts']), $post, ['forceDelete', 'restore', 'viewTrash']);
    }

    #[Test]
    public function it_maps_custom_abilities_and_denies_unknown_ones(): void
    {
        Gate::policy(Post::class, PostPolicy::class);

        $publisher = $this->createUser(['publish_posts']);
        $approver = $this->createUser(['approve_posts']);

        $this->assertTrue($publisher->can('publish', Post::class));
        $this->assertTrue($publisher->can('review', Post::class));
        $this->assertFalse($approver->can('publish', Post::class));
        $this->assertTrue($approver->can('review', Post::class));
        $this->assertFalse($publisher->can('somethingElse', Post::class));
        $this->assertFalse((new PostPolicy)->publish());
    }

    #[Test]
    public function guests_are_denied(): void
    {
        Gate::policy(Post::class, PostPolicy::class);

        $this->assertFalse(Gate::allows('viewAny', Post::class));
        $this->assertFalse(Gate::allows('publish', Post::class));
    }

    #[Test]
    public function owned_resources_need_the_others_permission_for_models_the_user_does_not_own(): void
    {
        Gate::policy(Post::class, OwnedPostPolicy::class);

        $author = $this->createUser(['edit_posts', 'delete_posts']);
        $other = $this->createUser(['edit_posts', 'delete_posts']);
        $editor = $this->createUser(['edit_others_posts', 'delete_others_posts']);

        $post = $this->createPost($author);

        $this->assertAbilities($author, $post, ['create', 'view', 'update', 'delete', 'restore', 'viewTrash']);
        $this->assertAbilities($other, $post, ['create', 'viewTrash']);
        $this->assertAbilities($editor, $post, ['view', 'update', 'delete', 'restore']);
    }

    #[Test]
    public function models_without_an_owner_check_are_not_owned(): void
    {
        $policy = new OwnedPostPolicy;
        $user = $this->createUser(['edit_posts']);

        $this->assertFalse($policy->update($user, $this->createUser()));
    }

    #[Test]
    public function a_policy_with_a_guard_checks_that_guards_permissions(): void
    {
        Gate::policy(Post::class, AdminPostPolicy::class);

        $admin = Admin::query()->create(['name' => 'ފާތިމަތު', 'email' => 'fathimath@example.com']);
        $admin->givePermissionTo('view_posts');

        $this->assertTrue($admin->can('viewAny', Post::class));
        $this->assertFalse($admin->can('create', Post::class));
    }

    #[Test]
    public function it_exposes_the_permission_name_for_an_action(): void
    {
        $this->assertEquals('edit_others_posts', (new PostPolicy)->permission('edit_others'));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    protected function createUser(array $permissions = []): User
    {
        static $count = 0;

        $count++;

        $user = User::query()->create(['name' => "ޔޫސަރ {$count}", 'email' => "user{$count}@example.com"]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    protected function createPost(?User $author = null): Post
    {
        $post = new Post(['title' => 'ޓެސްޓް ޕޯސްޓް']);
        $post->author()->associate($author);
        $post->save();

        return $post;
    }

    /**
     * @param  array<int, string>  $allowed
     */
    protected function assertAbilities(User $user, Post $post, array $allowed): void
    {
        $abilities = [
            'viewAny' => Post::class,
            'viewTrash' => Post::class,
            'create' => Post::class,
            'view' => $post,
            'update' => $post,
            'delete' => $post,
            'restore' => $post,
            'forceDelete' => $post,
        ];

        foreach ($abilities as $ability => $argument) {
            $this->assertEquals(
                in_array($ability, $allowed, true),
                $user->can($ability, $argument),
                "Unexpected result for {$ability}",
            );
        }
    }
}
