<?php

namespace Jiny\Post\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 파일 업로드 보안 검증 서비스
 */
class FileSecurityValidator
{
    /**
     * 위험한 파일 확장자 목록
     */
    private const DANGEROUS_EXTENSIONS = [
        // 실행 파일
        'exe', 'com', 'bat', 'cmd', 'scr', 'pif', 'msi', 'msp',

        // 스크립트 파일
        'php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'jspx',
        'cgi', 'pl', 'py', 'rb', 'sh', 'ps1', 'vbs', 'js', 'jar',

        // 서버 설정 파일
        'htaccess', 'htpasswd', 'ini', 'conf', 'config',

        // 기타 위험 파일
        'reg', 'lnk', 'url', 'ws', 'wsf', 'wsh', 'hta'
    ];

    /**
     * 허용된 이미지 확장자
     */
    private const ALLOWED_IMAGE_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'svg', 'ico'
    ];

    /**
     * 허용된 문서 확장자
     */
    private const ALLOWED_DOCUMENT_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'hwp', 'txt', 'rtf', 'odt', 'ods', 'odp'
    ];

    /**
     * 허용된 압축 파일 확장자
     */
    private const ALLOWED_ARCHIVE_EXTENSIONS = [
        'zip', 'rar', '7z', 'tar', 'gz', 'bz2'
    ];

    /**
     * 위험한 MIME 타입 목록
     */
    private const DANGEROUS_MIME_TYPES = [
        'application/x-executable',
        'application/x-msdownload',
        'application/x-dosexec',
        'application/x-php',
        'text/x-php',
        'application/php',
        'text/php'
    ];

    /**
     * 파일 검증
     *
     * @param UploadedFile $file
     * @param array $allowedExtensions
     * @param array $blockedExtensions
     * @param int $maxSize (KB)
     * @return array
     */
    public function validate(
        UploadedFile $file,
        array $allowedExtensions = [],
        array $blockedExtensions = [],
        int $maxSize = 5120
    ): array {
        $errors = [];

        // 기본 파일 검증
        if (!$file->isValid()) {
            $errors[] = '업로드된 파일이 유효하지 않습니다.';
            return ['valid' => false, 'errors' => $errors];
        }

        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();

        // 파일명 검증
        $filenameErrors = $this->validateFilename($originalName);
        $errors = array_merge($errors, $filenameErrors);

        // 확장자 검증
        $extensionErrors = $this->validateExtension($extension, $allowedExtensions, $blockedExtensions);
        $errors = array_merge($errors, $extensionErrors);

        // MIME 타입 검증
        $mimeErrors = $this->validateMimeType($mimeType, $extension);
        $errors = array_merge($errors, $mimeErrors);

        // 파일 크기 검증
        $sizeErrors = $this->validateFileSize($fileSize, $maxSize * 1024);
        $errors = array_merge($errors, $sizeErrors);

        // 파일 내용 검증
        $contentErrors = $this->validateFileContent($file, $extension);
        $errors = array_merge($errors, $contentErrors);

        // 로그 기록
        if (!empty($errors)) {
            Log::warning('파일 업로드 보안 검증 실패', [
                'filename' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize,
                'errors' => $errors,
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'metadata' => [
                'original_name' => $originalName,
                'extension' => $extension,
                'mime_type' => $mimeType,
                'size' => $fileSize
            ]
        ];
    }

    /**
     * 파일명 검증
     */
    private function validateFilename(string $filename): array
    {
        $errors = [];

        // 빈 파일명
        if (empty($filename)) {
            $errors[] = '파일명이 비어있습니다.';
            return $errors;
        }

        // 파일명 길이 제한
        if (strlen($filename) > 255) {
            $errors[] = '파일명이 너무 깁니다. (최대 255자)';
        }

        // 위험한 문자 검사
        $dangerousChars = ['<', '>', ':', '"', '|', '?', '*', '\\', '/', "\0"];
        foreach ($dangerousChars as $char) {
            if (strpos($filename, $char) !== false) {
                $errors[] = '파일명에 허용되지 않는 문자가 포함되어 있습니다.';
                break;
            }
        }

        // 숨김 파일 검사
        if (Str::startsWith($filename, '.')) {
            $errors[] = '숨김 파일은 업로드할 수 없습니다.';
        }

        // 이중 확장자 검사
        if (substr_count($filename, '.') > 1) {
            $parts = explode('.', $filename);
            foreach ($parts as $part) {
                if (in_array(strtolower($part), self::DANGEROUS_EXTENSIONS)) {
                    $errors[] = '이중 확장자가 포함된 파일은 업로드할 수 없습니다.';
                    break;
                }
            }
        }

        return $errors;
    }

    /**
     * 확장자 검증
     */
    private function validateExtension(string $extension, array $allowedExtensions, array $blockedExtensions): array
    {
        $errors = [];

        // 확장자가 없는 경우
        if (empty($extension)) {
            $errors[] = '파일 확장자가 없습니다.';
            return $errors;
        }

        // 위험한 확장자 검사
        if (in_array($extension, self::DANGEROUS_EXTENSIONS)) {
            $errors[] = "'{$extension}' 확장자는 보안상 업로드할 수 없습니다.";
        }

        // 차단된 확장자 검사
        if (!empty($blockedExtensions) && in_array($extension, $blockedExtensions)) {
            $errors[] = "'{$extension}' 확장자는 업로드가 차단되었습니다.";
        }

        // 허용된 확장자 검사 (설정되어 있는 경우)
        if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
            $errors[] = "'{$extension}' 확장자는 허용되지 않습니다.";
        }

        return $errors;
    }

    /**
     * MIME 타입 검증
     */
    private function validateMimeType(string $mimeType, string $extension): array
    {
        $errors = [];

        // 위험한 MIME 타입 검사
        if (in_array($mimeType, self::DANGEROUS_MIME_TYPES)) {
            $errors[] = '업로드할 수 없는 파일 형식입니다.';
        }

        // 확장자와 MIME 타입 일치성 검사
        $expectedMimes = $this->getExpectedMimeTypes($extension);
        if (!empty($expectedMimes) && !in_array($mimeType, $expectedMimes)) {
            $errors[] = '파일 확장자와 내용이 일치하지 않습니다.';
        }

        return $errors;
    }

    /**
     * 파일 크기 검증
     */
    private function validateFileSize(int $fileSize, int $maxSize): array
    {
        $errors = [];

        if ($fileSize > $maxSize) {
            $maxSizeKB = round($maxSize / 1024);
            $fileSizeKB = round($fileSize / 1024);
            $errors[] = "파일 크기가 너무 큽니다. (현재: {$fileSizeKB}KB, 최대: {$maxSizeKB}KB)";
        }

        return $errors;
    }

    /**
     * 파일 내용 검증
     */
    private function validateFileContent(UploadedFile $file, string $extension): array
    {
        $errors = [];

        try {
            // 파일 시그니처 검증
            $fileHandle = fopen($file->getPathname(), 'rb');
            if ($fileHandle) {
                $header = fread($fileHandle, 10);
                fclose($fileHandle);

                // 실행 파일 시그니처 검사
                $executableSignatures = [
                    "\x4D\x5A", // PE/COFF executable
                    "\x7F\x45\x4C\x46", // ELF executable
                    "\xCA\xFE\xBA\xBE", // Java class file
                ];

                foreach ($executableSignatures as $signature) {
                    if (Str::startsWith($header, $signature)) {
                        $errors[] = '실행 파일은 업로드할 수 없습니다.';
                        break;
                    }
                }

                // 스크립트 시그니처 검사
                if (Str::startsWith($header, '<?php') || Str::startsWith($header, '<%')) {
                    $errors[] = '스크립트 파일은 업로드할 수 없습니다.';
                }
            }

            // 이미지 파일 검증
            if (in_array($extension, self::ALLOWED_IMAGE_EXTENSIONS)) {
                $imageErrors = $this->validateImageFile($file);
                $errors = array_merge($errors, $imageErrors);
            }

        } catch (\Exception $e) {
            Log::error('파일 내용 검증 중 오류', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage()
            ]);
            $errors[] = '파일 검증 중 오류가 발생했습니다.';
        }

        return $errors;
    }

    /**
     * 이미지 파일 검증
     */
    private function validateImageFile(UploadedFile $file): array
    {
        $errors = [];

        try {
            $imageInfo = getimagesize($file->getPathname());
            if ($imageInfo === false) {
                $errors[] = '유효한 이미지 파일이 아닙니다.';
            } else {
                // 이미지 크기 제한
                $maxWidth = 5000;
                $maxHeight = 5000;

                if ($imageInfo[0] > $maxWidth || $imageInfo[1] > $maxHeight) {
                    $errors[] = "이미지 크기가 너무 큽니다. (최대: {$maxWidth}x{$maxHeight})";
                }
            }
        } catch (\Exception $e) {
            $errors[] = '이미지 파일 검증 중 오류가 발생했습니다.';
        }

        return $errors;
    }

    /**
     * 확장자별 예상 MIME 타입 반환
     */
    private function getExpectedMimeTypes(string $extension): array
    {
        $mimeMap = [
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'txt' => ['text/plain'],
            'zip' => ['application/zip'],
            'rar' => ['application/x-rar-compressed'],
        ];

        return $mimeMap[$extension] ?? [];
    }

    /**
     * 안전한 파일명 생성
     */
    public function generateSafeFilename(string $originalName): string
    {
        $pathInfo = pathinfo($originalName);
        $extension = strtolower($pathInfo['extension'] ?? '');
        $filename = $pathInfo['filename'] ?? 'file';

        // 파일명 정리
        $filename = preg_replace('/[^a-zA-Z0-9가-힣_-]/', '_', $filename);
        $filename = preg_replace('/_+/', '_', $filename);
        $filename = trim($filename, '_');

        if (empty($filename)) {
            $filename = 'file';
        }

        // 유니크한 파일명 생성
        $timestamp = date('YmdHis');
        $random = Str::random(8);

        return $filename . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    /**
     * 파일 검증 (컨트롤러 호환용 메소드)
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     */
    public function validateFile(UploadedFile $file, array $options = []): array
    {
        // 옵션에서 설정 추출
        $blockedExtensions = isset($options['blocked_extensions'])
            ? array_map('trim', explode(',', $options['blocked_extensions']))
            : [];

        $maxFileSize = $options['max_file_size'] ?? 5120; // KB
        $allowedTypes = $options['allowed_types'] ?? [];

        // 허용된 MIME 타입이 설정된 경우, 해당하는 확장자 추출
        $allowedExtensions = [];
        if (!empty($allowedTypes)) {
            foreach ($allowedTypes as $mimeType) {
                switch ($mimeType) {
                    case 'image/jpeg':
                        $allowedExtensions = array_merge($allowedExtensions, ['jpg', 'jpeg']);
                        break;
                    case 'image/png':
                        $allowedExtensions[] = 'png';
                        break;
                    case 'image/gif':
                        $allowedExtensions[] = 'gif';
                        break;
                    case 'image/jpg':
                        $allowedExtensions[] = 'jpg';
                        break;
                }
            }
        }

        // 기존 validate 메소드 호출
        $result = $this->validate($file, $allowedExtensions, $blockedExtensions, $maxFileSize);

        // 결과 형식을 컨트롤러에서 기대하는 형식으로 변환
        return [
            'valid' => $result['valid'],
            'error' => $result['valid'] ? null : implode(', ', $result['errors']),
            'errors' => $result['errors'] ?? []
        ];
    }
}