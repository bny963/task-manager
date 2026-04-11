<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTaskTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function タスク一覧をJSON形式で取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Task::factory()->count(3)->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->getJson('/api/tasks');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function タスク一覧のJSONレスポンス構造が正しい(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);
        Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'テストタスク',
            'priority' => 2,
        ]);

        // Act
        $response = $this->getJson('/api/tasks');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'priority',
                    'priority_label',
                    'category' => [
                        'id',
                        'name',
                    ],
                ],
            ],
        ]);
    }

    /** @test */
    public function タスク一覧のJSONレスポンス内容が正しい(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);
        $taskLow = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '低優先度タスク',
            'priority' => 1,
        ]);
        $taskMedium = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '中優先度タスク',
            'priority' => 2,
        ]);

        // Act
        $response = $this->getJson('/api/tasks');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $taskLow->id,
            'title' => '低優先度タスク',
            'priority' => 1,
            'priority_label' => '低',
        ]);
        $response->assertJsonFragment([
            'id' => $taskMedium->id,
            'title' => '中優先度タスク',
            'priority' => 2,
            'priority_label' => '中',
        ]);
        $response->assertJsonFragment([
            'name' => 'テストカテゴリー',
        ]);
    }

    /** @test */
    public function タスクが0件の場合は空の配列を返す(): void
    {
        // Act
        $response = $this->getJson('/api/tasks');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
        $response->assertJson(['data' => []]);
    }

    /** @test */
    public function 特定のタスクをJSON形式で取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'テストカテゴリー']);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'テストタスク',
            'description' => 'テストの説明',
            'priority' => 3,
        ]);

        // Act
        $response = $this->getJson("/api/tasks/{$task->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'title',
                'description',
                'priority',
                'priority_label',
                'category' => [
                    'id',
                    'name',
                ],
            ],
        ]);
    }

    /** @test */
    public function 特定のタスクのJSONレスポンス内容が正しい(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => '仕事']);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => '重要なタスク',
            'description' => 'これは重要なタスクです',
            'priority' => 3,
        ]);

        // Act
        $response = $this->getJson("/api/tasks/{$task->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'data' => [
                'id' => $task->id,
                'title' => '重要なタスク',
                'description' => 'これは重要なタスクです',
                'priority' => 3,
                'priority_label' => '高',
                'category' => [
                    'id' => $category->id,
                    'name' => '仕事',
                ],
            ],
        ]);
    }

    /** @test */
    public function 存在しないタスクIDで404エラーを返す(): void
    {
        // Act
        $response = $this->getJson('/api/tasks/99999');

        // Assert
        $response->assertNotFound(); // 404
    }

    /** @test */
    public function 無効なタスクIDで404エラーを返す(): void
    {
        // Act
        $response = $this->getJson('/api/tasks/invalid');

        // Assert
        $response->assertStatus(404);
    }
}
