@@ .. @@
                                        <?php if ($_SESSION['user_role'] === 'Dispatcher' && $plan['status'] === 'director_approved'): ?>
                                            <button onclick="showScheduleModal(<?php echo $plan['id']; ?>)" 
                                                    class="btn btn-outline-primary" title="Schedule">
                                                <i class="fas fa-calendar"></i>
                                            </button>
                                        <?php endif; ?>