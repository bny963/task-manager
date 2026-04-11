<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnauthenticatedRedirectTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 未認証ユーザーはタスク一覧にアクセスするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('tasks.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはタスク作成画面にアクセスするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('tasks.create'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはカテゴリー一覧にアクセスするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('categories.index'));

        // Assert
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function 未認証ユーザーはカテゴリー作成画面にアクセスするとログインページにリダイレクトされる(): void
    {
        // Act
        $response = $this->get(route('categories.create'));

        // Assert
        $response->assertRedirect(route('login'));
    }
}
