<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'document',
        'email',
        'phone',
        'responsible',
        'responsible_email',
        'responsible_phone',
        'state_registration',
        'municipal_registration',
        'zip_code',
        'address',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'country',
        'status',
        'notes',
    ];

    protected $casts = [
        'type'   => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Constantes para tipos de cliente
    public const TYPE_INDIVIDUAL = 'Pessoa Física';
    public const TYPE_LEGAL = 'Pessoa Jurídica';

    // Constantes para status
    public const STATUS_ACTIVE = 'ativo';
    public const STATUS_INACTIVE = 'inativo';

    // Accessors
    public function getFormattedDocumentAttribute(): string
    {
        $document = preg_replace('/\D/', '', $this->document);
        
        if (strlen($document) === 11) {
            // CPF: 000.000.000-00
            return substr($document, 0, 3) . '.' . 
                   substr($document, 3, 3) . '.' . 
                   substr($document, 6, 3) . '-' . 
                   substr($document, 9, 2);
        } elseif (strlen($document) === 14) {
            // CNPJ: 00.000.000/0000-00
            return substr($document, 0, 2) . '.' . 
                   substr($document, 2, 3) . '.' . 
                   substr($document, 5, 3) . '/' . 
                   substr($document, 8, 4) . '-' . 
                   substr($document, 12, 2);
        }
        
        return $this->document;
    }

    public function getFormattedPhoneAttribute(): ?string
    {
        if (!$this->phone) return null;
        
        $phone = preg_replace('/\D/', '', $this->phone);
        
        if (strlen($phone) === 11) {
            // Celular: (00) 90000-0000
            return '(' . substr($phone, 0, 2) . ') ' . 
                   substr($phone, 2, 5) . '-' . 
                   substr($phone, 7, 4);
        } elseif (strlen($phone) === 10) {
            // Fixo: (00) 0000-0000
            return '(' . substr($phone, 0, 2) . ') ' . 
                   substr($phone, 2, 4) . '-' . 
                   substr($phone, 6, 4);
        }
        
        return $this->phone;
    }

    public function getFormattedZipCodeAttribute(): ?string
    {
        if (!$this->zip_code) return null;
        
        $zipCode = preg_replace('/\D/', '', $this->zip_code);
        
        if (strlen($zipCode) === 8) {
            return substr($zipCode, 0, 5) . '-' . substr($zipCode, 5, 3);
        }
        
        return $this->zip_code;
    }

    public function getFullAddressAttribute(): string
    {
        $address = $this->address;
        if ($this->number) $address .= ', ' . $this->number;
        if ($this->complement) $address .= ', ' . $this->complement;
        if ($this->neighborhood) $address .= ' - ' . $this->neighborhood;
        if ($this->city) $address .= ' - ' . $this->city;
        if ($this->state) $address .= '/' . $this->state;
        if ($this->zip_code) $address .= ' - CEP: ' . $this->formatted_zip_code;
        
        return trim($address, ' -');
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('document', 'like', "%{$search}%")
              ->orWhere('responsible', 'like', "%{$search}%");
        });
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    // Métodos auxiliares
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isLegalPerson(): bool
    {
        return $this->type === self::TYPE_LEGAL;
    }

    public function isIndividual(): bool
    {
        return $this->type === self::TYPE_INDIVIDUAL;
    }

    // Validação de documento
    public static function validateDocument(string $document): bool
    {
        $document = preg_replace('/\D/', '', $document);
        
        if (strlen($document) === 11) {
            return self::validateCPF($document);
        } elseif (strlen($document) === 14) {
            return self::validateCNPJ($document);
        }
        
        return false;
    }

    private static function validateCPF(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private static function validateCNPJ(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14) {
            return false;
        }

        $weights1 = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weights2 = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += $cnpj[$i] * $weights1[$i];
        }
        $digit1 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += $cnpj[$i] * $weights2[$i];
        }
        $digit2 = $sum % 11 < 2 ? 0 : 11 - ($sum % 11);

        return $cnpj[12] == $digit1 && $cnpj[13] == $digit2;
    }
}
