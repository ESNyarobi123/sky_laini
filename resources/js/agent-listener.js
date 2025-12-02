import './echo';

/**
 * Listen for new job assignments for a specific agent.
 * 
 * @param {number} agentId - The ID of the logged-in agent.
 * @param {function} onJobReceived - Callback function when a job is received.
 */
export function listenForJobs(agentId, onJobReceived) {
    console.log(`Listening for jobs on channel: agents.${agentId}`);

    window.Echo.private(`agents.${agentId}`)
        .listen('LineRequestAssigned', (e) => {
            console.log('New Job Received:', e);
            onJobReceived(e);
        });
}

// Example usage:
// listenForJobs(currentAgentId, (job) => {
//     alert(`New Job! Request #${job.request_number}`);
//     // Update UI or play sound here
// });
