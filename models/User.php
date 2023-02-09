<?php

class User {
    private $name, $email, $expertise, $password;

    public function __construct(string $name, string $email, string $expertise, string $password, string $conf_password) {
        $this->password = $this->processPassword($password, $conf_password);
        $this->name = $name;
        $this->email = $this->processEmail($email);
        $this->expertise = $expertise;
    }

    public function store() {
        $sql = "INSERT INTO teachers (name, email, expertise, password) VALUES (?, ?, ?, ?)";
        $values = array($this->name, $this->email, $this->expertise, $this->password);

        (new DB())->execute($sql, $values);
    }

    public static function getById($id) {
        $sql = "SELECT * FROM teachers WHERE id = ?";
        $values = array($id);

        return (new DB())->select($sql, $values);
    }

    public static function getByEmail($email) : array {
        $sql = "SELECT * FROM teachers WHERE email = ?";
        $values = array($email);

        $user = (new DB())->execute($sql, $values);
        if (count($user) != 1) {
            throw new UserNotFoundError();
        }

        return $user[0];
    }

    public static function verifyCredentials(string $email, string $password): array {
        $user = User::getByEmail($email);
        $isCorrect = password_verify($password, $user["password"]);

        if (!$isCorrect) {
            throw new InvalidCredentialsError();
        }

        return $user;
    }

    private function verifyPasswordPattern(string $password) {
        // atleast 6 symbols, 1 letter, numbers
        $regex = "/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{6,}$/";

        if (!preg_match($regex, $password)) {
            throw new InvalidPasswordError();
        }
    }

    private function hashPassword(string $password): string {
        $hashedPassword = password_hash($password, PASSWORD_ARGON2I);
        $isCorrect = password_verify($password, $hashedPassword);

        if (!$isCorrect) {
            throw new HashingPasswordError();
        }

        return $hashedPassword;
    }

    private function processPassword(string $password, string $confpassword): string {
        if ($password !== $confpassword) {
            throw new PasswordMismatchError();
        }

        $this->verifyPasswordPattern($password);

        return $this->hashPassword($password);
    }

    private function processEmail(string $email): string {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDataError("email");
        }

        if ($this->hasDuplicate($email)) {
           throw new DuplicateItemError("Имейлът е зает");
        }

        return $email;
    }

    private function hasDuplicate(string $email) {
        $sql = "SELECT * FROM teachers WHERE email = ?";
        return DB::hasDuplicate($sql, [$email]);
    }

}
