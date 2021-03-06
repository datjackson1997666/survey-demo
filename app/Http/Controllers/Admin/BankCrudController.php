<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\BankRequest;
use App\Models\Bank;
use App\Models\Template;
use App\Models\User;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Support\Facades\Auth;

/**
 * Class BankCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BankCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Bank::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/survey');
        CRUD::setEntityNameStrings('survey', 'Surveys');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $user = Auth::guard('backpack')->user();
        $userIsAdmin = $user->is_admin;
        $this->crud->column('name')->type('string');
        $this->crud->column('url')->type('string');
        $this->crud->column('is_enable')->label('Enable')->type('boolean');
        if($userIsAdmin) {
            $this->crud->addColumn([
                'name' => 'user_id',
                'entity' => 'user',
                'model' => User::class,
                'attribute' => 'name',
                'multiple' => false,
                'pivot' => false,
            ]);
        }
        else {
            $this->crud->addClause('where', 'user_id', '=', $user->id);
        }
        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number']);
         */
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        $user = Auth::guard('backpack')->user();
        $userIsAdmin = $user->is_admin;
        CRUD::setValidation(BankRequest::class);

        //General settings
        $this->crud->addField([
            'name'=> 'name',
            'type' => 'text',
            'allows_null' => false,
            'tab' => 'General'
        ]);
        $this->crud->field('url')->type('url')->tab('General');
        $this->crud->field('is_enable')->label('Enable')->type('checkbox')->tab('General');
        $this->crud->addField([
            'name' => 'type',
            'type' => 'select_from_array',
            'multiple' => false,
            'pivot' => false,
            'allows_null' => false,
            'options'         => ['iframe' => 'Iframe', 'template' => 'Template'],
            'default' => 'iframe',
            'tab' => 'General'
        ]);
        $this->crud->field('survey_url')->label('Survey URL')->type('url')->tab('General')->hint('Insert this if type is "iframe"');
        $templates = Template::all(['id', 'name'])->toArray();
        $templateOptions = [];
        foreach($templates as $template) {
            $templateOptions[$template['id']] = $template['name'];
        }
        $this->crud->addField([   // select2_from_array
            'name'        => 'template_id',
            'label'       => "Template",
            'type'        => 'select2_from_array',
            'options'     => $templateOptions,
            'allows_null' => true,
            'tab' => 'General'
        ]);
        $this->crud->addField([   // Table
            'name'            => 'variables',
            'label'           => 'variables',
            'type'            => 'table',
            'entity_singular' => 'option', // used on the "Add X" button
            'columns'         => [
                'name'  => 'Name',
                'value'  => 'Description',
            ],
            'tab' => 'General'
        ]);
        if($userIsAdmin) {
            $this->crud->addField([
                'name' => 'user_id',
                'type' => 'select2',
                'entity' => 'user',
                'model' => User::class,
                'attribute' => 'name',
                'multiple' => false,
                'pivot' => false,
                'allows_null' => false,
                'tab' => 'General'
            ]);
        }
        else {
            $this->crud->addField([
                'name' => 'user_id',
                'type' => 'hidden',
                'entity' => 'user',
                'model' => User::class,
                'attribute' => 'name',
                'multiple' => false,
                'pivot' => false,
                'allows_null' => false,
                'default' => $user->id,
                'value' => $user->id,
                'tab' => 'General'
            ]);
        }
        // Button Settings
        $this->crud->field('button_text')->label('Button Text')->type('text')->tab( 'Button' )->hint('Set empty string to hide the button');
        $this->crud->field('button_color')->label('Button Color')->type('color')->tab('Button');
        $this->crud->field('button_bg_color')->label('Button Background Color')->type('color')->tab('Button');
        $this->crud->field('button_position')->label('Button Position')->type('enum')->tab('Button');

        // Trigger settings
        $this->crud->addField([
            'name' => 'show_when_hover_id',
            'label' => 'Hover ID',
            'type' => 'text',
            'hint' => 'ID of the element that will show the popup when hover over',
            'allows_null' => true,
            'tab' => 'Trigger'
        ]);
        $this->crud->field('max_show_on_hover_times')
            ->label('Max show on hover times')
            ->hint('Maximum number of times the popup shows when hover over Hover ID element')
            ->type('number')
            ->attributes([
                'min' => 1,
                'allows_null' => true
            ])
            ->default(null)
            ->tab('Trigger');
        $this->crud->field('popup_timeout')->label('Popup timeout (in seconds)')->hint('Show popup after (s) seconds')->type('number')->attributes([
            'allows_null' => true,
        ])->tab('Trigger');
        $this->crud->field('close_after_submit')->label('Close after submit')->hint('Close after user submitted (miliseconds)')->type('number')->attributes([
            'allows_null' => true,
            'min' => 1,
        ])->tab('Trigger');
        $this->crud->field('show_when_hover_id')->label('Hover ID')->hint('ID of the element that will show the popup when hover over')->attributes([
            'allows_null' => true
        ]);

        // Styling settings
        $this->crud->addField([
            'name'      => 'header_img_url',
            'label'     => 'Header Image',
            'hint'      => 'Image show on top of the survey',
            'type'      => 'upload',
            'upload'    => true,
            'disk'      => 'public',
            'tab'       => 'Styling'
        ]);
        $this->crud->field('close_btn_title')->label('Close Button Text')->type('text')->default('Close')->tab('Styling');
        $this->crud->field('popup_type')->label('Popup Type')->type('enum')->tab('Styling');
        $this->crud->field('backdrop_opacity')->label('Backdrop Opacity')->hint('0.01 to 0.99')->type('text')->default('1')->tab('Styling');

        /**
         * Fields can be defined using the fluent syntax or array syntax:
         * - CRUD::field('price')->type('number');
         * - CRUD::addField(['name' => 'price', 'type' => 'number']));
         */
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    protected function setupShowOperation() {
        $this->crud->column('url')->type('string');
        $this->crud->column('name');
        $this->crud->column('is_enable')->type('boolean');
        $this->crud->addColumn([
            'name' => 'user_id',
            'entity' => 'user',
            'model' => User::class,
            'attribute' => 'name',
            'multiple' => false,
            'pivot' => false,
        ]);
        $this->crud->column('button_text')->label('Button Text')->type('text');
        $this->crud->column('button_color')->label('Button Color')->type('color');
        $this->crud->column('button_position')->label('Button Position')->type('enum');
        $this->crud->column('popup_timeout')->label('Popup timeout (in seconds)')->type('number');
        $this->crud->column('show_when_hover_id')->label('Hover ID');
        $this->crud->column('max_show_on_hover_times')
            ->label('Max show on hover times')
            ->type('number');
        $this->crud->column('popup_type')
            ->label('Popup Type')
            ->type('text');
    }

    public function show($id) {
        $user = Auth::guard('backpack')->user();
        $userIsAdmin = $user->is_admin;
        $bank = Bank::query()->find($id);
        if(!$userIsAdmin && $user->id !== $bank->user_id) {
            \Alert::error(trans('auth.unauthorized-action'))->flash();
            return redirect('admin/bank');
        }

        // Original
        $this->crud->hasAccessOrFail('show');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $setFromDb = $this->crud->get('show.setFromDb');

        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.preview').' '.$this->crud->entity_name;

        // set columns from db
        if ($setFromDb) {
            $this->crud->setFromDb();
        }

        // cycle through columns
        foreach ($this->crud->columns() as $key => $column) {

            // remove any autoset relationship columns
            if (array_key_exists('model', $column) && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove any autoset table columns
            if ($column['type'] == 'table' && array_key_exists('autoset', $column) && $column['autoset']) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the row_number column, since it doesn't make sense in this context
            if ($column['type'] == 'row_number') {
                $this->crud->removeColumn($column['key']);
            }

            // remove columns that have visibleInShow set as false
            if (isset($column['visibleInShow']) && $column['visibleInShow'] == false) {
                $this->crud->removeColumn($column['key']);
            }

            // remove the character limit on columns that take it into account
            if (in_array($column['type'], ['text', 'email', 'model_function', 'model_function_attribute', 'phone', 'row_number', 'select'])) {
                $this->crud->modifyColumn($column['key'], ['limit' => ($column['limit'] ?? 999)]);
            }
        }

        // remove preview button from stack:line
        $this->crud->removeButton('show');

        // remove bulk actions colums
        $this->crud->removeColumns(['blank_first_column', 'bulk_actions']);

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getShowView(), $this->data);
    }

    public function destroy($id) {
        $user = Auth::guard('backpack')->user();
        $userIsAdmin = $user->is_admin;
        $bank = Bank::query()->find($id);
        if(!$userIsAdmin && $user->id !== $bank->user_id) {
            \Alert::error(trans('auth.unauthorized-action'))->flash();
            return redirect('admin/bank');
        }

        // Original
        $this->crud->hasAccessOrFail('delete');

        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;

        return $this->crud->delete($id);
    }

    public function edit($id)
    {
        $user = Auth::guard('backpack')->user();
        $userIsAdmin = $user->is_admin;
        $bank = Bank::query()->find($id);
        if(!$userIsAdmin && $user->id !== $bank->user_id) {
            \Alert::error(trans('auth.unauthorized-action'))->flash();
            return redirect('admin/bank');
        }

        // Original
        $this->crud->hasAccessOrFail('update');
        // get entry ID from Request (makes sure its the last ID for nested resources)
        $id = $this->crud->getCurrentEntryId() ?? $id;
        $this->crud->setOperationSetting('fields', $this->crud->getUpdateFields());
        // get the info for that entry
        $this->data['entry'] = $this->crud->getEntry($id);
        $this->data['crud'] = $this->crud;
        $this->data['saveAction'] = $this->crud->getSaveAction();
        $this->data['title'] = $this->crud->getTitle() ?? trans('backpack::crud.edit').' '.$this->crud->entity_name;

        $this->data['id'] = $id;

        // load the view from /resources/views/vendor/backpack/crud/ if it exists, otherwise load the one in the package
        return view($this->crud->getEditView(), $this->data);
    }
}
