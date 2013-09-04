<?php
class Af_Tapastic extends Plugin {
	private $host;

	function about() {
		return array(1.0,
			"Embeds Tapastic strips",
			"ldidry");
	}

	function init($host) {
		$this->host = $host;

		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["guid"], "tapastic.com") !== FALSE) {
			if (strpos($article["plugin_data"], "tapastic,$owner_uid:") === FALSE) {
				$link = str_replace("episode", "embed", $article["link"]);
				$article["link"] = $link;
				$doc = new DOMDocument();
				@$doc->loadHTML(fetch_file_contents($link));

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//article[@class="art-image-wrap"])');

					foreach ($entries as $entry) {
						$imgs  = $entry->childNodes;
						foreach ($imgs as $img) {
							$data = $img->getAttribute('data-src');
							if ($data !== '') {
								$img->setAttribute("src", $data);
							}
						}
						$basenode = $entry;
						break;
					}

					if ($basenode) {
						$article["content"] = $doc->saveXML($basenode);
						$article["plugin_data"] = "tapastic,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}

	function api_version() {
		return 2;
	}

}
?>
