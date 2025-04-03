<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
  </a>
</p>

<p align="center">
  <a href="https://github.com/laravel/framework/actions">
    <img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version">
  </a>
  <a href="https://packagist.org/packages/laravel/framework">
    <img src="https://img.shields.io/packagist/l/laravel/framework" alt="License">
  </a>
</p>

---

## 🚀 Requisitos para Rodar Localmente

Antes de iniciar, certifique-se de ter os seguintes requisitos instalados:

-   [Docker](https://www.docker.com/)
-   [PHP](https://www.php.net/)
-   [Composer](https://getcomposer.org/)

---

## 📌 Como Executar Localmente

1. Clone este repositório:

    ```sh
    git clone https://github.com/WindelAdmin/win_dfe_acbr_ms.git
    cd win_dfe_acbr_ms
    ```

2. Instale as dependências do projeto:

    ```sh
    composer install
    ```

3. Inicie os containers Docker:

    ```sh
    docker compose up -d
    ```

4. Caso precise gerar uma nova imagem do Dockerfile, utilize:
    ```sh
    docker image prune -a
    ```

---

## 📜 Licença

Este projeto segue a licença [MIT](https://opensource.org/licenses/MIT).
