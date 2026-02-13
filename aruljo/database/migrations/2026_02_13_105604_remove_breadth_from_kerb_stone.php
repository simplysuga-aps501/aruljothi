<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Product\Template;
use App\Models\Product\Parameter;
use App\Models\Product\ParameterConfig;
use App\Models\Product\ParameterUnitConfig;

return new class extends Migration {
    public function up(): void
    {
        // ====== Fetch IDs ======
        $templateId = Template::where('name', 'kerb stone')->value('id');
        $breadthId  = Parameter::where('name', 'Breadth')->value('id');

        // ====== 1️⃣ Delete Breadth from ParameterConfig ======
        if ($templateId && $breadthId) {
            ParameterConfig::where('prod_template_id', $templateId)
                ->where('prod_parameter_id', $breadthId)
                ->delete();
        }

        // ====== 2️⃣ Delete Breadth from ParameterUnitConfig ======
        if ($templateId && $breadthId) {
            ParameterUnitConfig::where('prod_template_id', $templateId)
                ->where('prod_parameter_id', $breadthId)
                ->delete();
        }
    }

    public function down(): void
    {
        // ====== Fetch IDs again for rollback ======
        $templateId = Template::where('name', 'kerb stone')->value('id');
        $breadthId  = Parameter::where('name', 'Breadth')->value('id');

        // ====== 1️⃣ Restore Breadth mapping if rolled back ======
        if ($templateId && $breadthId) {
            ParameterConfig::create([
                'prod_template_id'  => $templateId,
                'prod_parameter_id' => $breadthId,
                'input_type'        => 'numeric',
                'is_required'       => true,
                'sort_order'        => 2,
                'modified_by'       => 1,
            ]);
        }

        // ====== 2️⃣ Restore Breadth → Unit mapping if rolled back ======
        if ($templateId && $breadthId) {
            ParameterUnitConfig::create([
                'prod_template_id'       => $templateId,
                'prod_parameter_id'      => $breadthId,
                'prod_parameter_unit_id' => null, // optional — assign correct ID if known
                'allow_custom_unit'      => 0,
                'modified_by'            => 1,
            ]);
        }
    }
};
