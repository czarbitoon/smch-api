<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ticket;
use App\Models\Device;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    /**
     * Create a new helpdesk ticket
     */
    public function createTicket(Request $request)
    {
        // Add fallbacks for mobile app payloads
        if ($request->has('title') && !$request->has('subject')) {
            $request->merge(['subject' => $request->input('title')]);
        }
        if ($request->has('report_image') && !$request->has('image')) {
            $request->merge(['image' => $request->input('report_image')]);
        }

        try {
            // Validate the request
            $validatedData = $request->validate([
                'subject' => 'required|string|max:255',
                'description' => 'required|string',
                'device_id' => 'required|exists:devices,id',
                'priority' => 'sometimes|in:low,medium,high',
                'image' => $request->hasFile('image') ? 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' : 'nullable|string',
            ], [
                'subject.required' => 'The ticket subject is required',
                'subject.max' => 'The subject must not exceed 255 characters',
                'description.required' => 'The description is required',
                'device_id.required' => 'A device must be selected',
                'device_id.exists' => 'The selected device does not exist',
                'priority.in' => 'Invalid priority. Must be one of: low, medium, high',
                'image.image' => 'The file must be an image',
                'image.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif',
                'image.max' => 'The image must not be larger than 2MB',
            ]);

            // Get authenticated user
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            // Verify device exists
            $device = Device::findOrFail($validatedData['device_id']);

            // Handle image upload if provided
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $image->getClientOriginalExtension();
                $path = $image->storeAs('ticket_images', $filename, 'public');
                $imagePath = $path;
            } elseif ($request->has('image') && is_string($request->input('image'))) {
                $imagePath = $request->input('image');
            }

            // Create ticket
            $ticket = Ticket::create([
                'subject' => $validatedData['subject'],
                'description' => $validatedData['description'],
                'device_id' => $device->id,
                'user_id' => $user->id,
                'office_id' => $user->office_id ?? $device->office_id,
                'priority' => $validatedData['priority'] ?? Ticket::PRIORITY_MEDIUM,
                'status' => Ticket::STATUS_OPEN,
                'image' => $imagePath,
            ]);

            return response()->json([
                'message' => 'Ticket created successfully',
                'ticket' => $ticket->load(['device', 'reporter', 'office', 'assignedTechnician'])
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::info('Ticket validation failed: ' . json_encode([
                'input' => $request->all(),
                'errors' => $e->errors()
            ]));
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Device not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in createTicket: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while creating the ticket'
            ], 500);
        }
    }

    /**
     * Get all tickets (with role-based filtering)
     */
    public function getTickets(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $query = Ticket::with(['device', 'reporter', 'office', 'assignedTechnician']);

            // Role-based filtering
            if (in_array($user->role, ['admin', 'superadmin'])) {
                // Admin and superadmin can see all tickets
            } elseif ($user->role === 'staff') {
                // Staff can see tickets from their office OR assigned to them
                $query->where(function ($q) use ($user) {
                    $q->where('office_id', $user->office_id)
                      ->orWhere('assigned_to', $user->id);
                });
            } else {
                // Regular users can only see their own tickets
                $query->where('user_id', $user->id);
            }

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->input('status'));
            }

            if ($request->has('priority')) {
                $query->where('priority', $request->input('priority'));
            }

            if ($request->has('office_id')) {
                $query->where('office_id', $request->input('office_id'));
            }

            if ($request->has('assigned_to')) {
                $query->where('assigned_to', $request->input('assigned_to'));
            }

            // Sorting
            $orderBy = $request->input('order_by', 'created_at');
            $direction = $request->input('direction', 'desc');
            
            if (in_array($orderBy, ['created_at', 'priority', 'status'])) {
                $query->orderBy($orderBy, $direction);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            $tickets = $query->get();
            return response()->json($tickets);

        } catch (\Exception $e) {
            Log::error('Error in getTickets: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while fetching tickets'
            ], 500);
        }
    }

    /**
     * Get a single ticket by ID
     */
    public function getTicket($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ticket = Ticket::with(['device', 'reporter', 'office', 'assignedTechnician'])
                ->findOrFail($id);

            // Role-based authorization
            $this->authorizeView($user, $ticket);

            return response()->json($ticket);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            if ($e->getCode() === 403) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }
            Log::error('Error in getTicket: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Update ticket status
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ticket = Ticket::findOrFail($id);

            // Only staff/admin can update status
            if (!in_array($user->role, ['staff', 'admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'Unauthorized. Only staff, admin, or superadmin can update ticket status'
                ], 403);
            }

            $validatedData = $request->validate([
                'status' => 'required|in:open,in-progress,resolved,closed',
                'resolution_notes' => 'nullable|string',
            ], [
                'status.required' => 'Status is required',
                'status.in' => 'Invalid status. Must be one of: open, in-progress, resolved, closed',
            ]);

            // Update status and related fields
            $ticket->status = $validatedData['status'];

            if ($validatedData['status'] === 'in-progress' && !$ticket->started_at) {
                $ticket->started_at = now();
                $ticket->assigned_to = $user->id;
            }

            if ($validatedData['status'] === 'resolved' || $validatedData['status'] === 'closed') {
                $ticket->resolved_at = now();
                if ($validatedData['resolution_notes'] ?? null) {
                    $ticket->resolution_notes = $validatedData['resolution_notes'];
                }
            }

            $ticket->save();

            return response()->json([
                'message' => 'Ticket status updated successfully',
                'ticket' => $ticket->load(['device', 'reporter', 'office', 'assignedTechnician'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in updateStatus: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred while updating the ticket'
            ], 500);
        }
    }

    /**
     * Assign ticket to a technician
     */
    public function assignTicket(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ticket = Ticket::findOrFail($id);

            // Only staff/admin can assign
            if (!in_array($user->role, ['staff', 'admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $validatedData = $request->validate([
                'assigned_to' => 'required|exists:users,id',
            ]);

            // Verify the technician exists and is staff/admin
            $technician = User::findOrFail($validatedData['assigned_to']);
            if (!in_array($technician->role, ['staff', 'admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'The assigned user must be staff, admin, or superadmin'
                ], 422);
            }

            $ticket->assigned_to = $validatedData['assigned_to'];
            $ticket->save();

            return response()->json([
                'message' => 'Ticket assigned successfully',
                'ticket' => $ticket->load(['device', 'reporter', 'office', 'assignedTechnician'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ticket or technician not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in assignTicket: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Update ticket (description, priority)
     */
    public function updateTicket(Request $request, $id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ticket = Ticket::findOrFail($id);

            // Only creator, assigned technician, or admin can update
            if ($user->id !== $ticket->user_id && 
                $user->id !== $ticket->assigned_to && 
                !in_array($user->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $validatedData = $request->validate([
                'subject' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'priority' => 'sometimes|in:low,medium,high',
            ]);

            $ticket->fill($validatedData);
            $ticket->save();

            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => $ticket->load(['device', 'reporter', 'office', 'assignedTechnician'])
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation error',
                'details' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in updateTicket: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Delete a ticket
     */
    public function deleteTicket($id)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $ticket = Ticket::findOrFail($id);

            // Only creator or admin can delete
            if ($user->id !== $ticket->user_id && !in_array($user->role, ['admin', 'superadmin'])) {
                return response()->json([
                    'error' => 'Unauthorized'
                ], 403);
            }

            $ticket->delete();
            return response()->json([
                'message' => 'Ticket deleted successfully'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Ticket not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error in deleteTicket: ' . $e->getMessage());
            return response()->json([
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Helper: Authorize ticket view
     */
    private function authorizeView($user, $ticket)
    {
        if (in_array($user->role, ['admin', 'superadmin'])) {
            return; // Admins can see all
        }

        if ($user->role === 'staff') {
            if ($ticket->office_id === $user->office_id || $ticket->assigned_to === $user->id) {
                return; // Staff can see their office tickets
            }
        }

        if ($user->id === $ticket->user_id) {
            return; // Users can see their own tickets
        }

        // Not authorized
        throw new \Exception('Unauthorized', 403);
    }
}
