<?php
	class Search {

		private $input_dir;
		private $output_dir;
		private $arrSellers;

		public function __construct() {
			$this->input_dir = 'data/in';
			$this->output_dir = 'data/out';
			$this->arrSellers = array();
		}

		public function colectData() {

			$dir = new DirectoryIterator(realpath($this->input_dir));
			
			foreach($dir as $fileinfo) {
			    if(!$fileinfo->isDot()) {
			    	$filename = $fileinfo->getFilename();
			    	$pathFile = $this->input_dir.'/'.$filename;
			    	$ext = pathinfo($filename, PATHINFO_EXTENSION);
			    	$modelSellers = new Sellers();
					$modelConsumers = new Consumers();
					$modelSales = new Sales();

			    	if(in_array($ext, array('dat'))) {
			    		$file_handle = fopen($pathFile, 'r');
						while(!feof($file_handle)) {
						   $line = fgets($file_handle);

						   $arrLine = explode('ç', $line);
						   if($arrLine[0] == '001') {
							   	$cpf = $arrLine[1];
							   	$name = $arrLine[2];
							   	$salary = $arrLine[3];
							   	$modelSellers->addSalesman($cpf, $name, $salary);
						   }
						   elseif($arrLine[0] == '002') {
						   	$cnpj = $arrLine[1];
						   	$name = $arrLine[2];
						   	$business_area = $arrLine[3];

						   	$modelConsumers->addConsumer($cnpj, $name, $business_area);
						   } elseif($arrLine[0] == '003') {
						   	$id = $arrLine[1];
						   	$items = $arrLine[2];
						   	$salesman_name = $arrLine[3];

						   	$modelSales->addSale($id, $salesman_name, $items);

						   }
						}
						fclose($file_handle);

						$arrSellersAmount = $modelSales->getSellersAmount();
						foreach($arrSellersAmount as $obj) {
							$modelSellers->updateTotalSales($obj->name, $obj->total);
						}

						$this->totalConsumers = $modelConsumers->getTotalConsumers();
						$this->totalSellers = $modelSellers->getTotalSellers();
						$this->expensivesaleID = $modelSales->getMostExpensiveSale()->id;
						$this->worstSellerName = $modelSellers->getWorstSeller()->name;
						$this->generateReport(basename($filename, '.'.$ext));
					}
			    }
			}
		}

		private function generateReport($filename) {
			$arrReportsLines = array();
			array_push($arrReportsLines, 'Number of clients: '.$this->totalConsumers);
			array_push($arrReportsLines, 'Number of salesman: '.$this->totalSellers);
			array_push($arrReportsLines, 'The most expensive sale is the sale: '.$this->expensivesaleID);
			array_push($arrReportsLines, 'The worst salesman ever is: '.$this->worstSellerName);
			
			$content = implode(PHP_EOL, $arrReportsLines);
			$reportFilename = $filename.'.done.dat';

			$this->saveFile($reportFilename, $content);
		}

		private function saveFile($filename, $content) {
			$fullpathFile = $this->output_dir.'/'.$filename;
			$objFile = fopen($fullpathFile, 'w');
			fwrite($objFile, $content);
			fclose($objFile);
		}
		
		private function lerArquivoTexto($strArquivo) {
			if(is_file($strArquivo)) {
				$objTxt = fopen( $strArquivo, "r" );
				$texto = fread( $objTxt, filesize( $strArquivo ) );
				fclose($objTxt);
				return $texto;
			}
		}
	}
?>