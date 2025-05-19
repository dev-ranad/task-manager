<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $statusOptions = ['pending', 'in-progress', 'hold', 'review', 'cancel', 'completed'];
        $status = fake()->randomElement($statusOptions);

        return [
            'title' => fake()->unique()->sentence(3),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(['Low', 'Medium', 'High']),
            'status' => $status,
            'completed_at' => $status === 'completed' ? now() : null,
            'user_id' => User::factory(),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Task $task) {
            $task->categories()->attach(Category::factory()->count(1)->create());
            $task->users()->attach(User::factory()->count(1)->create());
        });
    }
}
