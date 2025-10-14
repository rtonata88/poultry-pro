<?php

namespace App\Livewire\Operations;

use App\Models\Farm;
use App\Models\FeedInventory as FeedInventoryModel;
use App\Models\FeedReceipt;
use App\Models\FeedType;
use Livewire\Component;
use Livewire\WithPagination;

class FeedInventory extends Component
{
    use WithPagination;

    // Feed Type Management
    public $showFeedTypeForm = false;
    public $editingFeedTypeId = null;
    public $feedTypeName = '';
    public $feedTypeDescription = '';
    public $feedTypeUnit = 'kg';

    // Feed Receipt Management
    public $showReceiptForm = false;
    public $editingReceiptId = null;
    public $receiptFeedTypeId = '';
    public $receiptFarmId = '';
    public $receiptDate = '';
    public $receiptQuantity = 0;
    public $receiptSupplier = '';
    public $receiptUnitPrice = 0;
    public $receiptTotalCost = 0;
    public $receiptNotes = '';

    // Filters
    public $filterFarm = '';

    public function mount()
    {
        $this->receiptDate = now()->format('Y-m-d');
    }

    public function updatedReceiptQuantity()
    {
        $this->calculateReceiptTotal();
    }

    public function updatedReceiptUnitPrice()
    {
        $this->calculateReceiptTotal();
    }

    private function calculateReceiptTotal()
    {
        $this->receiptTotalCost = (float)$this->receiptQuantity * (float)$this->receiptUnitPrice;
    }

    // Feed Type CRUD
    public function saveFeedType(): void
    {
        $validated = $this->validate([
            'feedTypeName' => 'required|string|max:255',
            'feedTypeDescription' => 'nullable|string',
            'feedTypeUnit' => 'required|in:kg,bags',
        ]);

        if ($this->editingFeedTypeId) {
            FeedType::find($this->editingFeedTypeId)->update([
                'name' => $validated['feedTypeName'],
                'description' => $validated['feedTypeDescription'],
                'unit' => $validated['feedTypeUnit'],
            ]);
            session()->flash('status', 'Feed type updated successfully.');
        } else {
            FeedType::create([
                'name' => $validated['feedTypeName'],
                'description' => $validated['feedTypeDescription'],
                'unit' => $validated['feedTypeUnit'],
            ]);
            session()->flash('status', 'Feed type created successfully.');
        }

        $this->cancelFeedType();
    }

    public function editFeedType($id): void
    {
        $feedType = FeedType::findOrFail($id);
        $this->editingFeedTypeId = $id;
        $this->feedTypeName = $feedType->name;
        $this->feedTypeDescription = $feedType->description ?? '';
        $this->feedTypeUnit = $feedType->unit;
        $this->showFeedTypeForm = true;
    }

    public function deleteFeedType($id): void
    {
        FeedType::findOrFail($id)->delete();
        session()->flash('status', 'Feed type deleted successfully.');
    }

    public function cancelFeedType(): void
    {
        $this->reset(['feedTypeName', 'feedTypeDescription', 'feedTypeUnit', 'editingFeedTypeId', 'showFeedTypeForm']);
    }

    // Feed Receipt CRUD
    public function saveReceipt(): void
    {
        $validated = $this->validate([
            'receiptFeedTypeId' => 'required|exists:feed_types,id',
            'receiptFarmId' => 'nullable|exists:farms,id',
            'receiptDate' => 'required|date|before_or_equal:today',
            'receiptQuantity' => 'required|numeric|min:0.01',
            'receiptSupplier' => 'nullable|string|max:255',
            'receiptUnitPrice' => 'nullable|numeric|min:0',
            'receiptTotalCost' => 'nullable|numeric|min:0',
            'receiptNotes' => 'nullable|string',
        ]);

        $data = [
            'feed_type_id' => $validated['receiptFeedTypeId'],
            'farm_id' => $validated['receiptFarmId'],
            'date' => $validated['receiptDate'],
            'quantity' => $validated['receiptQuantity'],
            'supplier' => $validated['receiptSupplier'],
            'unit_price' => $validated['receiptUnitPrice'],
            'total_cost' => $validated['receiptTotalCost'],
            'notes' => $validated['receiptNotes'],
        ];

        if ($this->editingReceiptId) {
            $receipt = FeedReceipt::find($this->editingReceiptId);
            $oldQuantity = $receipt->quantity;
            $receipt->update($data);

            // Update inventory
            $this->updateInventoryOnReceipt($receipt, $oldQuantity);

            session()->flash('status', 'Feed receipt updated successfully.');
        } else {
            $receipt = FeedReceipt::create($data);

            // Update inventory
            $this->updateInventoryOnReceipt($receipt);

            session()->flash('status', 'Feed receipt recorded successfully.');
        }

        $this->cancelReceipt();
    }

    private function updateInventoryOnReceipt(FeedReceipt $receipt, $oldQuantity = 0)
    {
        $inventory = FeedInventoryModel::firstOrCreate(
            [
                'feed_type_id' => $receipt->feed_type_id,
                'farm_id' => $receipt->farm_id,
            ],
            [
                'current_stock' => 0,
                'reorder_level' => 0,
            ]
        );

        // Adjust stock: remove old quantity if editing, add new quantity
        $inventory->current_stock = $inventory->current_stock - $oldQuantity + $receipt->quantity;
        $inventory->save();
    }

    public function editReceipt($id): void
    {
        $receipt = FeedReceipt::findOrFail($id);
        $this->editingReceiptId = $id;
        $this->receiptFeedTypeId = $receipt->feed_type_id;
        $this->receiptFarmId = $receipt->farm_id ?? '';
        $this->receiptDate = $receipt->date->format('Y-m-d');
        $this->receiptQuantity = $receipt->quantity;
        $this->receiptSupplier = $receipt->supplier ?? '';
        $this->receiptUnitPrice = $receipt->unit_price ?? 0;
        $this->receiptTotalCost = $receipt->total_cost ?? 0;
        $this->receiptNotes = $receipt->notes ?? '';
        $this->showReceiptForm = true;
    }

    public function deleteReceipt($id): void
    {
        $receipt = FeedReceipt::findOrFail($id);

        // Update inventory
        $inventory = FeedInventoryModel::where('feed_type_id', $receipt->feed_type_id)
            ->where('farm_id', $receipt->farm_id)
            ->first();

        if ($inventory) {
            $inventory->current_stock -= $receipt->quantity;
            $inventory->save();
        }

        $receipt->delete();
        session()->flash('status', 'Feed receipt deleted successfully.');
    }

    public function cancelReceipt(): void
    {
        $this->reset(['receiptFeedTypeId', 'receiptFarmId', 'receiptQuantity', 'receiptSupplier', 'receiptUnitPrice', 'receiptTotalCost', 'receiptNotes', 'editingReceiptId', 'showReceiptForm']);
        $this->receiptDate = now()->format('Y-m-d');
        $this->receiptUnitPrice = 0;
        $this->receiptTotalCost = 0;
    }

    public function render()
    {
        $feedTypesQuery = FeedType::where('is_active', true)->orderBy('name');
        $feedTypes = $feedTypesQuery->get();

        $inventoryQuery = FeedInventoryModel::with(['feedType', 'farm']);

        if ($this->filterFarm) {
            $inventoryQuery->where('farm_id', $this->filterFarm);
        }

        $receiptsQuery = FeedReceipt::with(['feedType', 'farm'])->latest('date');

        if ($this->filterFarm) {
            $receiptsQuery->where('farm_id', $this->filterFarm);
        }

        return view('livewire.operations.feed-inventory', [
            'feedTypes' => $feedTypes,
            'inventory' => $inventoryQuery->get(),
            'receipts' => $receiptsQuery->paginate(15),
            'farms' => Farm::where('is_active', true)->orderBy('name')->get(),
        ]);
    }
}
