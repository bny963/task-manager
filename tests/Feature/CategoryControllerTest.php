<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function ユーザーはカテゴリー一覧を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        Category::factory()->count(3)->create();

        // Act
        $response = $this->actingAs($user)->get(route('categories.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('categories');
    }

    /** @test */
    public function ユーザーはカテゴリー詳細を取得できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('categories.show', $category));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('category');
    }

    /** @test */
    public function ユーザーはカテゴリー作成画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('categories.create'));

        // Assert
        $response->assertStatus(200);
    }

    /** @test */
    public function ユーザーはカテゴリーを作成できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => 'テストカテゴリー',
        ]);

        // Assert
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'テストカテゴリー',
        ]);
    }

    /** @test */
    public function カテゴリー名が空だとバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => '',
        ]);

        // Assert
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function カテゴリー名は255文字まで入力できる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => str_repeat('あ', 255),
        ]);

        // Assert
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => str_repeat('あ', 255),
        ]);
    }

    /** @test */
    public function カテゴリー名が256文字以上だとバリデーションエラーになる(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->post(route('categories.store'), [
            'name' => str_repeat('あ', 256),
        ]);

        // Assert
        $response->assertSessionHasErrors('name');
    }

    /** @test */
    public function ユーザーはカテゴリー編集画面を表示できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('categories.edit', $category));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('category');
    }

    /** @test */
    public function ユーザーはカテゴリーを更新できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($user)->put(route('categories.update', $category), [
            'name' => '更新後のカテゴリー名',
        ]);

        // Assert
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => '更新後のカテゴリー名',
        ]);
    }

    /** @test */
    public function ユーザーはカテゴリーを削除できる(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();

        // Act
        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        // Assert
        $response->assertRedirect(route('categories.index'));
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function タスクが紐づいているカテゴリーは削除できない(): void
    {
        // Arrange
        $user = User::factory()->create();
        $category = Category::factory()->create();
        // カテゴリーにタスクを紐づける
        Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        // Act
        $response = $this->actingAs($user)->delete(route('categories.destroy', $category));

        // Assert
        $response->assertRedirect(route('categories.index'));
        $response->assertSessionHas('error'); // エラーメッセージがセッションに含まれる
        $this->assertDatabaseHas('categories', ['id' => $category->id]); // 削除されていない
    }
}
