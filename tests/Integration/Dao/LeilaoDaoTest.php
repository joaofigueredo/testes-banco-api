<?php

namespace Alura\Leilao\Tests\Integration\Dao;


use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Infra\ConnectionCreator;
use Alura\Leilao\Model\Leilao;
use PHPUnit\Framework\TestCase;

class LeilaoDaoTest extends TestCase
{
    /** @var \PDO */
    private static  $pdo;

    public static function setUpBeforeClass(): void
    {
        self::$pdo = new \PDO('sqlite::memory:');
        self::$pdo->exec('create table leiloes(
                    id INTEGER
                    primary key,
                    descricao TEXT,
                    finalizado BOOL,
                    dataInicio TEXT
                );');
    }

    protected function setUp(): void
    {
        self::$pdo->beginTransaction();
    }

    /**
     * @dataProvider leiloes
     */
    public function testBuscaLeilaoNaoFinalizado(array $leiloes)
    {
        $leilaoDao = new LeilaoDao(self::$pdo);

        foreach ($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        $leiloes = $leilaoDao->recuperarNaoFinalizados();

        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame(
            'Variant 0km',
            $leiloes[0]->recuperarDescricao()
        );
    }
    /**
     * @dataProvider leiloes
     * */
    public function testBuscaLeilaoFinalizado(array $leiloes)
    {
        $leilaoDao = new LeilaoDao(self::$pdo);
        foreach($leiloes as $leilao) {
            $leilaoDao->salva($leilao);
        }

        $leiloes = $leilaoDao->recuperarFinalizados();

        self::assertCount(1, $leiloes);
        self::assertContainsOnlyInstancesOf(Leilao::class, $leiloes);
        self::assertSame(
            'Fiat 147 0km',
            $leiloes[0]->recuperarDescricao()
        );
    }

    public function testAoAtualizarLeilaoStatusDeveSerAlterado ()
    {
        $leilao = new Leilao('Brasilia amarela');

        $leilaoDao = new leilaoDao(self::$pdo);
        $leilao = $leilaoDao->salva($leilao);

        $leiloes = $leilaoDao->recuperarNaoFinalizados();
        self::assertCount(1, $leiloes);
        self::assertSame(
            'Brasilia amarela',
            $leiloes[0]->recuperarDescricao());
        self::assertFalse($leiloes[0]->estaFinalizado());

        $leilao->finaliza();
        $leilaoDao->atualiza($leilao);
        
        $leiloes = $leilaoDao->recuperarFinalizados();
        self::assertCount(1, $leiloes);
        self::assertSame(
            'Brasilia amarela',
            $leiloes[0]->recuperarDescricao());
        self::assertTrue($leiloes[0]->estaFinalizado());
    }

    protected function tearDown(): void
    {
        self::$pdo->rollBack();
    }

    public function leiloes()
    {
        $naoFinalizado = new Leilao('Variant 0km');
        $finalizado = new Leilao('Fiat 147 0km');
        $finalizado->finaliza();
        return [
            [
                [$naoFinalizado, $finalizado]
            ]
        ];
    }

}