<?php
namespace SLSupplyHub;

use PDO;
use Exception;

class Loyalty extends Model {
    protected $table = 'loyalty_rewards';
    protected $fillable = ['customer_id', 'transaction_count', 'tier', 'reward_amount'];
    
    private $tiers = [
        'None' => [
            'min_transactions' => 0,
            'reward_rate' => 0
        ],
        'Bronze' => [
            'min_transactions' => 5,
            'reward_rate' => 0.01 // 1%
        ],
        'Silver' => [
            'min_transactions' => 10,
            'reward_rate' => 0.02 // 2%
        ],
        'Gold' => [
            'min_transactions' => 20,
            'reward_rate' => 0.03 // 3%
        ]
    ];
    
    public function initializeCustomerRewards($customerId) {
        try {
            return $this->create([
                'customer_id' => $customerId,
                'transaction_count' => 0,
                'tier' => 'None',
                'reward_amount' => 0
            ]);
        } catch (\Exception $e) {
            error_log("Initialize rewards error: " . $e->getMessage());
            return false;
        }
    }
    
    public function processOrderRewards($customerId, $orderAmount) {
        try {
            $this->beginTransaction();
            
            // Get current loyalty status
            $loyalty = $this->where('customer_id = ?', [$customerId])[0] ?? null;
            if (!$loyalty) {
                // Initialize rewards if not exists
                $this->initializeCustomerRewards($customerId);
                $loyalty = $this->where('customer_id = ?', [$customerId])[0];
            }
            
            // Increment transaction count
            $newCount = $loyalty['transaction_count'] + 1;
            
            // Calculate new tier
            $newTier = $this->calculateTier($newCount);
            
            // Calculate rewards for this transaction
            $rewardRate = $this->tiers[$newTier]['reward_rate'];
            $newRewardAmount = $loyalty['reward_amount'] + ($orderAmount * $rewardRate);
            
            // Update loyalty record
            $this->update($loyalty['id'], [
                'transaction_count' => $newCount,
                'tier' => $newTier,
                'reward_amount' => $newRewardAmount
            ]);
            
            $this->commit();
            
            return [
                'success' => true,
                'new_tier' => $newTier,
                'reward_amount' => $newRewardAmount,
                'points_earned' => $orderAmount * $rewardRate
            ];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Process rewards error: " . $e->getMessage());
            return ['error' => 'Failed to process rewards'];
        }
    }
    
    private function calculateTier($transactionCount) {
        $tier = 'None';
        foreach ($this->tiers as $tierName => $requirements) {
            if ($transactionCount >= $requirements['min_transactions']) {
                $tier = $tierName;
            }
        }
        return $tier;
    }
    
    public function useRewards($customerId, $amount) {
        try {
            $this->beginTransaction();
            
            $loyalty = $this->where('customer_id = ?', [$customerId])[0] ?? null;
            if (!$loyalty || $loyalty['reward_amount'] < $amount) {
                return ['error' => 'Insufficient reward balance'];
            }
            
            $newRewardAmount = $loyalty['reward_amount'] - $amount;
            
            $this->update($loyalty['id'], [
                'reward_amount' => $newRewardAmount
            ]);
            
            $this->commit();
            
            return [
                'success' => true,
                'remaining_balance' => $newRewardAmount
            ];
            
        } catch (\Exception $e) {
            $this->rollback();
            error_log("Use rewards error: " . $e->getMessage());
            return ['error' => 'Failed to use rewards'];
        }
    }
    
    public function getCustomerRewards($customerId) {
        try {
            $loyalty = $this->where('customer_id = ?', [$customerId])[0] ?? null;
            if (!$loyalty) {
                return [
                    'tier' => 'None',
                    'transaction_count' => 0,
                    'reward_amount' => 0,
                    'next_tier' => 'Bronze',
                    'transactions_to_next_tier' => 5
                ];
            }
            
            $nextTier = $this->getNextTier($loyalty['tier']);
            $transactionsToNext = $nextTier ? 
                $this->tiers[$nextTier]['min_transactions'] - $loyalty['transaction_count'] : 
                0;
            
            return [
                'tier' => $loyalty['tier'],
                'transaction_count' => $loyalty['transaction_count'],
                'reward_amount' => $loyalty['reward_amount'],
                'next_tier' => $nextTier,
                'transactions_to_next_tier' => $transactionsToNext
            ];
            
        } catch (\Exception $e) {
            error_log("Get rewards error: " . $e->getMessage());
            return ['error' => 'Failed to retrieve rewards information'];
        }
    }
    
    private function getNextTier($currentTier) {
        $tiers = array_keys($this->tiers);
        $currentIndex = array_search($currentTier, $tiers);
        
        return isset($tiers[$currentIndex + 1]) ? $tiers[$currentIndex + 1] : null;
    }
    
    public function getTierBenefits($tier) {
        if (!isset($this->tiers[$tier])) {
            return null;
        }
        
        return [
            'tier' => $tier,
            'min_transactions' => $this->tiers[$tier]['min_transactions'],
            'reward_rate' => $this->tiers[$tier]['reward_rate'],
            'reward_percentage' => $this->tiers[$tier]['reward_rate'] * 100 . '%'
        ];
    }
    
    public function getAllTiers() {
        $tiersInfo = [];
        foreach ($this->tiers as $tier => $requirements) {
            $tiersInfo[] = [
                'tier' => $tier,
                'min_transactions' => $requirements['min_transactions'],
                'reward_rate' => $requirements['reward_rate'],
                'reward_percentage' => $requirements['reward_rate'] * 100 . '%'
            ];
        }
        return $tiersInfo;
    }
}