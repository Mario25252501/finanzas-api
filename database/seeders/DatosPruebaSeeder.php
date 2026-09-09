<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatosPruebaSeeder extends Seeder
{
    private const MESES_CON_DATOS = [1, 2, 3, 4, 5, 7];

    public function run(): void
    {
        $this->call(CategoriaSeeder::class);

        $categoriasIngreso = Categoria::query()
            ->whereNull('user_id')
            ->where('tipo', 'ingreso')
            ->get()
            ->values();

        $categoriasEgreso = Categoria::query()
            ->whereNull('user_id')
            ->where('tipo', 'egreso')
            ->with('subcategorias')
            ->get()
            ->values();

        $usuarios = [
            [
                'name' => 'Andrea García',
                'email' => 'andrea.garcia@pruebas.local',
                'fuente' => 'Trabajo de medio tiempo',
                'ingresos' => [2600, 2650, 2700, 2600, 2750, 2800],
            ],
            [
                'name' => 'Luis Martínez',
                'email' => 'luis.martinez@pruebas.local',
                'fuente' => 'Proyectos freelance',
                'ingresos' => [1800, 2100, 1950, 2300, 2050, 2400],
            ],
        ];

        foreach ($usuarios as $indiceUsuario => $datosUsuario) {
            $usuario = User::query()->updateOrCreate(
                ['email' => $datosUsuario['email']],
                [
                    'name' => $datosUsuario['name'],
                    'password' => Hash::make('finanzas2026'),
                    'email_verified_at' => now(),
                ],
            );

            $usuario->egresos()->delete();
            $usuario->ingresos()->delete();

            foreach (self::MESES_CON_DATOS as $indiceMes => $mes) {
                $categoriaIngreso = $categoriasIngreso[($indiceMes + $indiceUsuario) % $categoriasIngreso->count()];

                Ingreso::factory()->create([
                    'user_id' => $usuario->id,
                    'categoria_id' => $categoriaIngreso->id,
                    'fecha' => Carbon::create(2026, $mes, 5)->toDateString(),
                    'fuente' => $datosUsuario['fuente'],
                    'monto' => sprintf('%d.00', $datosUsuario['ingresos'][$indiceMes]),
                    'notas' => 'Ingreso de prueba para el dashboard.',
                ]);

                $cantidadEgresos = 6 + (($mes + $indiceUsuario) % 7);

                for ($indiceEgreso = 0; $indiceEgreso < $cantidadEgresos; $indiceEgreso++) {
                    $categoria = $categoriasEgreso[
                        ($indiceMes + $indiceEgreso + $indiceUsuario) % $categoriasEgreso->count()
                    ];
                    $subcategorias = $categoria->subcategorias;
                    $subcategoria = $subcategorias->isNotEmpty()
                        ? $subcategorias[($mes + $indiceEgreso + $indiceUsuario) % $subcategorias->count()]
                        : null;
                    $monto = $this->montoEgreso(
                        $categoria->nombre,
                        $mes,
                        $indiceEgreso,
                        $indiceUsuario,
                    );

                    Egreso::factory()->create([
                        'user_id' => $usuario->id,
                        'categoria_id' => $categoria->id,
                        'subcategoria_id' => $subcategoria?->id,
                        'fecha' => Carbon::create(2026, $mes, 3 + (($indiceEgreso * 3) % 24))->toDateString(),
                        'descripcion' => $subcategoria?->nombre ?? "Gasto en {$categoria->nombre}",
                        'monto' => sprintf('%d.00', $monto),
                        'notas' => 'Egreso de prueba para el dashboard.',
                    ]);
                }
            }
        }
    }

    private function montoEgreso(string $categoria, int $mes, int $indiceEgreso, int $indiceUsuario): int
    {
        $montosPorCategoria = [
            'Vivienda' => [450, 75, 120, 200],
            'Educación' => [500, 100, 150],
            'Alimentación' => [400, 80, 35],
            'Transporte' => [180, 10, 30, 15],
            'Salud' => [150, 90, 200],
            'Ocio / Entretenimiento' => [50, 65, 100],
            'Deporte' => [175, 250],
            'Imprevistos' => [350, 200],
            'Otro Egreso' => [100],
        ];
        $montos = $montosPorCategoria[$categoria];

        return $montos[($mes + $indiceEgreso + $indiceUsuario) % count($montos)];
    }
}
