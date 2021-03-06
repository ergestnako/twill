<?php

namespace A17\Twill\Commands;

use File;
use Illuminate\Console\Command;

class GenerateBlocks extends Command
{
    protected $signature = 'twill:blocks';

    protected $description = "Generate blocks as single file Vue components from blade views";

    public function handle()
    {
        $this->info("Starting to scan block views directory...");
        collect(File::files(resource_path('views/admin/blocks')))->each(function ($viewFile) {
            $blockName = $viewFile->getBasename('.blade.php');

            $vueBlockTemplate = view('admin.blocks.' . $blockName, ['renderForBlocks' => true])->render();

            $vueBlockContent = view('twill::blocks.builder', [
                'render' => $this->sanitize($vueBlockTemplate),
            ])->render();

            $vueBlockPath = resource_path('assets/js/blocks/') . 'Block' . title_case($blockName) . '.vue';

            File::put($vueBlockPath, $vueBlockContent);

            $this->info("Block " . title_case($blockName) . " generated successfully");
        });

        $this->info("All blocks have been generated!");
    }

    private function sanitize($html)
    {
        $search = array(
            '/\>[^\S ]+/s', // strip whitespaces after tags, except space
            '/[^\S ]+\</s', // strip whitespaces before tags, except space
            '/(\s)+/s', // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/', // Remove HTML comments
        );

        $replace = array(
            '>',
            '<',
            '\\1',
            '',
        );

        return preg_replace($search, $replace, $html);
    }

}
