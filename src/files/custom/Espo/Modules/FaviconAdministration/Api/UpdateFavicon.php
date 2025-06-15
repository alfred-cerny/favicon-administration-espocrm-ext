<?php

namespace Espo\Modules\FaviconAdministration\Api;

use Espo\Core\Api\Action;
use Espo\Core\Api\Request;
use Espo\Core\Api\Response;
use Espo\Core\Api\ResponseComposer;
use Espo\Core\Exceptions\BadRequest;
use Espo\Core\Exceptions\Forbidden;
use Espo\Core\Utils\File\Manager as FileManager;
use Espo\ORM\EntityManager;
use Espo\Entities\Attachment;
use Espo\Entities\User;

readonly class UpdateFavicon implements Action {
	public function __construct(
		private FileManager   $fileManager,
		private EntityManager $entityManager,
		private User          $user
	) {}

	public function process(Request $request): Response {
		if (!$this->user->isAdmin()) {
			throw new Forbidden('Admin access required');
		}

		$data = $request->getParsedBody();
		$attachmentId = $data->attachmentId ?? null;
		$targetPath = 'client/custom/modules/favicon-administration/img/favicon.ico';

		if (is_null($attachmentId)) {
			if ($this->fileManager->exists($targetPath)) {
				$this->fileManager->remove($targetPath);
			}

			return ResponseComposer::json(['success' => true]);
		}

		/** @var Attachment $attachment */
		$attachment = $this->entityManager->getEntityById(Attachment::ENTITY_TYPE, $attachmentId);

		if (!$attachment) {
			throw new BadRequest('Attachment not found');
		}

		/** @var \Espo\Repositories\Attachment $attachmentRepository */
		$attachmentRepository = $this->entityManager->getRepository(Attachment::ENTITY_TYPE);
		$sourcePath = $attachmentRepository->getFilePath($attachment);

		if (!$this->fileManager->isFile($sourcePath)) {
			throw new BadRequest('Source file not found');
		}

		$targetDir = dirname($targetPath);
		if (!$this->fileManager->isDir($targetDir)) {
			$this->fileManager->mkdir($targetDir, 0755, true);
		}

		$content = $this->fileManager->getContents($sourcePath);
		$this->fileManager->putContents($targetPath, $content);

		return ResponseComposer::json(['success' => true]);
	}

}