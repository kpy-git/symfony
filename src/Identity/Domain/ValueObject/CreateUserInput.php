<?php

namespace App\Identity\Domain\ValueObject;

use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\Ask;
use Symfony\Component\Validator\Constraints as Assert;

class CreateUserInput
{
    #[Argument]
    #[Ask('Nombre de usuario', constraints: [new Assert\NotBlank])]
    public string $username {
        set(string $value) {
            $this->username = strtolower(trim($value));
        }
    }

    #[Argument]
    #[Ask('Contraseña', hidden: true, constraints: [new Assert\NotBlank])]
    public string $password;

    #[Argument]
    #[Ask('Email', constraints: [new Assert\NotBlank, new Assert\Email])]
    public string $email {
        set(string $value) {
            $this->email = strtolower(trim($value));
        }
    }

    #[Argument]
    #[Ask('Nombre')]
    public string $firstName {
        set(string $value) {
            $this->firstName = ucwords(trim($value));
        }
    }

    #[Argument]
    #[Ask('Apellidos')]
    public string $lastName {
        set(string $value) {
            $this->lastName = ucwords(trim($value));
        }
    }

    #[Argument]
    #[Ask('Avatar (presiona Enter para usar el avatar por defecto)')]
    public string $avatar {
        set(?string $value) {
            $this->avatar = $value ?? '0.svg';
        }
    }

}
