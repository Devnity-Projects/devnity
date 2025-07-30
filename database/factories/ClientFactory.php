<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([Client::TYPE_INDIVIDUAL, Client::TYPE_LEGAL]);
        $isLegal = $type === Client::TYPE_LEGAL;

        return [
            'name' => $isLegal 
                ? $this->faker->company() . ' Ltda'
                : $this->faker->name(),
            'type' => $type,
            'document' => $isLegal 
                ? $this->generateCNPJ()
                : $this->generateCPF(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'responsible' => $isLegal ? $this->faker->name() : null,
            'responsible_email' => $isLegal ? $this->faker->safeEmail() : null,
            'responsible_phone' => $isLegal ? $this->faker->phoneNumber() : null,
            'state_registration' => $isLegal ? $this->faker->numerify('###.###.###.###') : null,
            'municipal_registration' => $isLegal ? $this->faker->numerify('########') : null,
            'zip_code' => $this->faker->numerify('#####-###'),
            'address' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'complement' => $this->faker->optional()->secondaryAddress(),
            'neighborhood' => $this->faker->citySuffix(),
            'city' => $this->faker->city(),
            'state' => $this->faker->stateAbbr(),
            'country' => 'Brasil',
            'status' => $this->faker->randomElement([Client::STATUS_ACTIVE, Client::STATUS_INACTIVE]),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Client::STATUS_ACTIVE,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Client::STATUS_INACTIVE,
        ]);
    }

    public function individual(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_INDIVIDUAL,
            'name' => $this->faker->name(),
            'document' => $this->generateCPF(),
            'responsible' => null,
            'responsible_email' => null,
            'responsible_phone' => null,
            'state_registration' => null,
            'municipal_registration' => null,
        ]);
    }

    public function legal(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Client::TYPE_LEGAL,
            'name' => $this->faker->company() . ' Ltda',
            'document' => $this->generateCNPJ(),
            'responsible' => $this->faker->name(),
            'responsible_email' => $this->faker->safeEmail(),
            'responsible_phone' => $this->faker->phoneNumber(),
            'state_registration' => $this->faker->numerify('###.###.###.###'),
            'municipal_registration' => $this->faker->numerify('########'),
        ]);
    }

    private function generateCPF(): string
    {
        // Gera um CPF válido simples para testes
        return '11144477735'; // CPF válido fixo para testes
    }

    private function generateCNPJ(): string
    {
        // Gera um CNPJ válido simples para testes
        return '11222333000181'; // CNPJ válido fixo para testes
    }
}
