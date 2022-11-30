<?php

class TaskController
{
    public function __construct(
        private TaskGateway $gateway,
        private int $user_id
    ) {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id === null) {
            if ($method == "GET") {
                echo json_encode($this->gateway->getAllForUser($this->user_id));
            } elseif ($method == "POST") {
                $data = (array) json_decode(file_get_contents("php://input"), true);

                // Data validation
                $errors = $this->getValidationErrors($data);
                if (!empty($errors)) {
                    $this->respondUnprocessableEntity($errors);
                    return;
                }

                $id = $this->gateway->createForUser($this->user_id, $data);
                $this->respondCreated($id);
            } else {
                $this->respondMethodNotAllowed("GET, POST");
            }
        } else {
            $task = $this->gateway->getForUser($this->user_id, $id);
            if ($task === false) {
                $this->respondNotFound($id);
                return;
            }
            switch ($method) {
                case ("GET"):
                    echo json_encode($task);
                    break;
                case ("PATCH"):
                    $data = (array) json_decode(file_get_contents("php://input"), true);

                    // Data validation
                    $errors = $this->getValidationErrors($data, false);
                    if (!empty($errors)) {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }
                    $row = $this->gateway->updateForUser($this->user_id, $id, $data);
                    echo json_encode(["message" => "Task Updated", "rows" => $row]);
                    break;
                case ("DELETE"):
                    $this->gateway->deleteForUser($this->user_id, $id);
                    echo json_encode(["message" => "Task deleted"]);
                    break;
                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
            }
        }
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound($id)
    {
        http_response_code(404);
        echo json_encode(["Message" => "Task with ID $id not found"]);
    }

    private function respondCreated($id): void
    {
        http_response_code(201);
        echo json_encode(["Message" => "Task Created", "id" => $id]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];
        if ($is_new && empty($data['name'])) {
            $errors[] = "Name is required";
        }
        if (!empty($data['priority'])) {
            if (filter_var($data['priority'], FILTER_VALIDATE_INT) === false) {
                $errors[] = "Priority must be an interger";
            }
        }
        return $errors;
    }
}
