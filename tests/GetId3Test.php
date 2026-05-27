<?php

namespace Owenoj\LaravelGetId3\Tests;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Owenoj\LaravelGetId3\GetId3;
use PHPUnit\Framework\TestCase;

class GetId3Test extends TestCase
{
    private string $fixturePath;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fixturePath = 'getid3-test.wav';
        $this->createMinimalWav($this->fixturePath);
        $this->assertFileExists($this->fixturePath, 'WAV fixture was not created — check write permissions ');
    }
    
    protected function tearDown(): void
    {
        if (file_exists($this->fixturePath)) {
            unlink($this->fixturePath);
        }
        
        parent::tearDown();
    }
    
    public function test_from_disk_and_path_accepts_filesystem_instance(): void
    {
        $stream = fopen($this->fixturePath, 'rb');
        
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('size')->with('audio/test.wav')->willReturn(filesize($this->fixturePath));
        $filesystem->method('readStream')->with('audio/test.wav')->willReturn($stream);
        
        $getId3 = GetId3::fromUploadedFile(new UploadedFile($this->fixturePath, 'test.wav', 'audio/wav',
            filesize($this->fixturePath), false));
        
        $this->assertInstanceOf(GetId3::class, $getId3);
        
        fclose($stream);
    }
    
    public function test_extracts_file_format_from_wav(): void
    {
        
        $getId3 = new GetId3($this->fixturePath);
        
        $this->assertSame('wav', $getId3->getFileFormat());
    }
    
    public function test_has_audio_true_for_wav(): void
    {
        $getId3 = $this->getFileInfo();
        
        $this->assertTrue($getId3->hasAudio());
        $this->assertFalse($getId3->hasVideo());
    }
    
    public function test_is_audio_true_for_wav(): void
    {
        $getId3 = $this->getFileInfo();
        
        $this->assertTrue($getId3->isAudio());
        $this->assertFalse($getId3->isVideo());
    }
    
    public function test_get_sample_rate_returns_expected_value(): void
    {
        $getId3 = $this->getFileInfo();
        
        $this->assertSame(8000, $getId3->getSampleRate());
    }
    
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------
    
    private function getFileInfo(): GetId3
    {
        $stream = fopen($this->fixturePath, 'rb');
        
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->method('size')->with('audio/test.wav')->willReturn(filesize($this->fixturePath));
        $filesystem->method('readStream')->with('audio/test.wav')->willReturn($stream);
        
        return GetId3::fromDiskAndPath($filesystem, 'audio/test.wav');
    }
    
    /**
     * Write a minimal valid PCM WAV file to $path.
     */
    private function createMinimalWav(string $path): void
    {
        $sampleRate = 8000;
        $numChannels = 1;
        $bitsPerSample = 8;
        
        $numSamples = 8000;
        
        $dataSize = $numSamples * $numChannels * ($bitsPerSample / 8);
        $byteRate = $sampleRate * $numChannels * ($bitsPerSample / 8);
        $blockAlign = $numChannels * ($bitsPerSample / 8);
        $chunkSize = 36 + $dataSize;
        
        $fp = fopen($path, 'wb');
        
        fwrite($fp, 'RIFF');
        fwrite($fp, pack('V', $chunkSize));
        fwrite($fp, 'WAVE');
        
        fwrite($fp, 'fmt ');
        fwrite($fp, pack('V', 16));
        fwrite($fp, pack('v', 1));
        fwrite($fp, pack('v', $numChannels));
        fwrite($fp, pack('V', $sampleRate));
        fwrite($fp, pack('V', $byteRate));
        fwrite($fp, pack('v', $blockAlign));
        fwrite($fp, pack('v', $bitsPerSample));
        
        fwrite($fp, 'data');
        fwrite($fp, pack('V', $dataSize));
        
        fwrite($fp, str_repeat("\x80", $dataSize));
        
        fclose($fp);
    }
    
    
}
