<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class CepController extends Controller
{
    /**
     * Buscar informações de endereço por CEP
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'cep' => 'required|string|size:8'
        ]);

        $cep = preg_replace('/\D/', '', $request->cep);

        if (strlen($cep) !== 8) {
            return response()->json([
                'error' => 'CEP deve conter exatamente 8 dígitos'
            ], 400);
        }

        try {
            $response = Http::timeout(10)->get("https://viacep.com.br/ws/{$cep}/json/");

            if (!$response->successful()) {
                return response()->json([
                    'error' => 'Erro ao consultar o CEP'
                ], 500);
            }

            $data = $response->json();

            if (isset($data['erro'])) {
                return response()->json([
                    'error' => 'CEP não encontrado'
                ], 404);
            }

            return response()->json([
                'cep' => $data['cep'] ?? null,
                'logradouro' => $data['logradouro'] ?? null,
                'complemento' => $data['complemento'] ?? null,
                'bairro' => $data['bairro'] ?? null,
                'localidade' => $data['localidade'] ?? null,
                'uf' => $data['uf'] ?? null,
                'ibge' => $data['ibge'] ?? null,
                'gia' => $data['gia'] ?? null,
                'ddd' => $data['ddd'] ?? null,
                'siafi' => $data['siafi'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
