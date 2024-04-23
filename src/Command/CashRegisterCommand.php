<?php

/* (c) Erin Hima <erinhima@gmail.com> */

namespace App\Command;

use App\Entity\Product;
use App\Entity\Receipt;
use App\Service\PriceFormater;
use App\Service\ProductManager;
use App\Service\ReceiptManager;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console cash-register:start
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console cash-register:start -v
 *
 * @author Erin Hima <erinhima@gmail.com>
 */
#[AsCommand(
    name: 'cash-register:start',
    description: 'Initiate the cash register',
)]
final class CashRegisterCommand extends Command
{
    private SymfonyStyle $io;

    /** @var array<int, array<string, mixed>> */
    private array $productData = [];
    /** @var string[] */
    private array $productNames = [];
    /** @var array<int, array<string, string, string, string>> */
    private array $productRows = [];

    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly ProductManager $productManager,
        private readonly ReceiptManager $receiptManager,
    ) {
        parent::__construct();

        $this->productData = $this->productManager->getProductTable();
    }

    protected function configure(): void
    {
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);

        foreach ($this->productData as $product) {
            $this->productNames[] = $product['name'];
            $this->productRows[] = [
                $product['barCode'],
                $product['name'],
                PriceFormater::format($product['listPriceAmount']),
                $product['listPriceType']
            ];
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->printAvailableProducts($output);

        $receipt = new Receipt();

        while (true) {
            $product = $this->askForProduct($input, $output);
            $this->io->writeln('');
            $this->io->success([sprintf('Product "%s" selected.', $product->getName())]);
            $quantity = $this->askForQuantity($product, $input, $output);
            $this->io->success(sprintf('Quantity "%s" selected.', $quantity));

            $this->receiptManager->addProductToReceipt($receipt, $product, $quantity);

            $this->io->info($product->getName() . ' added to receipt. quantity: +' . $quantity);
            $this->printAvailableProducts($output);
            $this->printReceipt($receipt, $output);
        }

        // TODO: $this->receiptManager->closeReceipt($receipt);
        return Command::SUCCESS;
    }


    private function printAvailableProducts(OutputInterface $output): void
    {
        $productsTable = new Table($output);
        $productsTable
            ->setHeaderTitle('Available Products')
            ->setHeaders(['Code', 'Name', 'List Price', 'Price Type'])
            ->setRows($this->productRows);
        $productsTable->render();

        $this->io->writeln('');
    }

    private function askForProduct(InputInterface $input, OutputInterface $output): Product
    {
        $helper = $this->getHelper('question');
        $product = null;

        while (!$product) {
            $question = new Question('<question>Please enter the product name ->: </question>');
            $question->setNormalizer(function (?string $value = null): string {
                $value = $value ? trim($value) : '';
                return ucfirst($value);
            });
            $question->setAutocompleterValues($this->productNames);
            $productName = $helper->ask($input, $output, $question);
            if (!empty($productName)) {
                $product = $this->productManager->getByName($productName);
                if (!$product) {
                    $this->io->error(sprintf('Product with name "%s" not found.', $productName));
                }
            }
        }

        return $product;
    }

    public function askForQuantity(Product $product, InputInterface $input, OutputInterface $output): int|float
    {
        $helper = $this->getHelper('question');
        $quantity = 0;

        while (!$quantity) {
            $question = new Question('Please enter the quantity ->: ');
            $question->setNormalizer(function (?string $value = null): string {
                return $value ? trim($value) : '';
            });
            $question->setValidator(function ($quantity) use ($product) {
                if (!is_numeric($quantity) || floatval($quantity) <= 0) {
                    throw new \RuntimeException('Quantity must be a positive number');
                }

                switch ($product->getListPriceType()) {
                    case Product::PRICE_TYPE_WEIGHT:
                        if (!intval($quantity)) {
                            throw new \RuntimeException('Quantity must be an integer');
                        }
                        break;
                    case Product::PRICE_TYPE_UNIT:
                        if (intval($quantity) != $quantity) {
                            throw new \RuntimeException('Scusate ma ' . $product->getName() . ' non li vendiamo sfusi');
                        }
                        break;
                }

                return $quantity;
            });

            $quantity = $helper->ask($input, $output, $question);
        }

        return $quantity;
    }

    private function printReceipt(Receipt $receipt, OutputInterface $output): void
    {
        $receiptLines = $receipt->getReceiptLines();
        $headers = ['Product', 'Quantity', 'Net Amount', 'Discount', 'Amount'];
        if ($output->isVerbose()) {
            $headers[] = 'Discount';
        }

        $rows = [];

        foreach ($receiptLines as $receiptLine) {
            $rows[] = $receiptLine->toArray($output->isVerbose());
        }
        $rows[] = new TableSeparator();
        $receiptInfo = $receipt->toArray($output->isVerbose());
        $footer = [];
        foreach ($receiptInfo as $key => $value) {
            $footer[] = $key . ': ' . $value;
        }
        $rows[] = $footer;

        $receiptTable = new Table($output);
        $receiptTable
            ->setHeaderTitle('Receipt')
            ->setHeaders($headers)
            ->setRows($rows);

        $receiptTable->render();

        $this->io->writeln('');
    }
}
