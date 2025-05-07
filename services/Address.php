<?php
namespace SLSupplyHub;

class Address extends Model {
    protected $table = 'addresses';
    protected $fillable = [
        'user_id', 'first_name', 'last_name', 'phone', 'email',
        'street', 'barangay', 'city', 'postal_code', 'is_default'
    ];
    
    protected $validationRules = [
        'user_id' => ['required', 'numeric'],
        'first_name' => ['required', 'max:100'],
        'last_name' => ['required', 'max:100'],
        'phone' => ['required', 'max:20'],
        'email' => ['required', 'max:255', 'email'],
        'street' => ['required', 'max:255'],
        'barangay' => ['required', 'max:100'],
        'city' => ['required', 'max:100'],
        'postal_code' => ['required', 'max:10']
    ];
    
    public function addAddress($data)
    {
        try {
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return ['error' => $errors];
            }

            $this->beginTransaction();

            // Reset default addresses if needed
            if ($this->isFirstAddress($data['user_id']) || ($data['is_default'] ?? false)) {
                $this->resetDefaultAddresses($data['user_id']);
                $data['is_default'] = true;
            }

            $addressId = $this->create($data);

            $this->commit();

            return ['success' => true];
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Address creation error: " . $e->getMessage());
            return ['error' => $e->getMessage()]; // Return specific error
        }
    }
    
    public function updateAddress($id, $data) {
        try {
            $errors = $this->validate($data);
            if (!empty($errors)) {
                return ['error' => $errors];
            }
            
            $this->beginTransaction();
            
            // Get current address
            $address = $this->find($id);
            if (!$address) {
                return ['error' => 'Address not found'];
            }
            
            // If setting as default
            if (($data['is_default'] ?? false) && !$address['is_default']) {
                $this->resetDefaultAddresses($data['user_id']);
            }
            
            // Update address
            $this->update($id, $data);
            
            $this->commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Address update error: " . $e->getMessage());
            return ['error' => 'Failed to update address'];
        }
    }
    
    public function deleteAddress($id, $userId) {
        try {
            $this->beginTransaction();
            
            // Get address
            $address = $this->find($id);
            if (!$address || $address['user_id'] != $userId) {
                return ['error' => 'Address not found'];
            }
            
            // Delete address
            $this->delete($id);
            
            // If deleted address was default, set another as default
            if ($address['is_default']) {
                $this->setNewDefaultAddress($userId);
            }
            
            $this->commit();
            
            return ['success' => true];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Address deletion error: " . $e->getMessage());
            return ['error' => 'Failed to delete address'];
        }
    }
    
    public function getUserAddresses($userId) {
        try {
            return $this->where('user_id = ? ORDER BY is_default DESC, created_at DESC', [$userId]);
        } catch (\Exception $e) {
            error_log("Get addresses error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve addresses'];
        }
    }
    
    public function getDefaultAddress($userId) {
        try {
            $addresses = $this->where('user_id = ? AND is_default = 1', [$userId]);
            return $addresses[0] ?? null;
        } catch (\Exception $e) {
            error_log("Get default address error: " . $e->getMessage());
            return null;
        }
    }
    
    private function isFirstAddress($userId) {
        $stmt = $this->db->getConnection()->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchColumn() === 0;
    }
    
    private function resetDefaultAddresses($userId) {
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE {$this->table} SET is_default = 0 WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }
    
    private function setNewDefaultAddress($userId) {
        // Get the most recently created address and set it as default
        $stmt = $this->db->getConnection()->prepare(
            "UPDATE {$this->table} 
            SET is_default = 1 
            WHERE user_id = ? 
            AND id = (
                SELECT id FROM (
                    SELECT id FROM {$this->table} 
                    WHERE user_id = ? 
                    ORDER BY created_at DESC 
                    LIMIT 1
                ) sub
            )"
        );
        $stmt->execute([$userId, $userId]);
    }
    
    public function formatAddress($address) {
        $parts = [
            $address['first_name'] . ' ' . $address['last_name'],
            $address['street'],
            $address['barangay'],
            $address['city'],
            $address['postal_code']
        ];
        
        // Filter out empty parts
        $parts = array_filter($parts);
        
        return implode(', ', $parts);
    }
}