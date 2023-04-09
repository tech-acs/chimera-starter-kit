export class Stack {
    items = [];

    constructor(items = [], mode = 'LIFO') {
        if (mode === 'FIFO') {
            for (let i = items.length - 1; i >= 0; i--) {
                this.items.push(items[i]);
            }
        } else {
            for (let i = 0; i < items.length; i++) {
                this.items.push(items[i]);
            }
        }
    }

    push(item) {
        this.items.push(item);
    }

    pop() {
        return this.items.pop();
    }

    isEmpty() {
        return this.items.length === 0;
    }

    size() {
        return this.items.length;
    }
}

export class DoublyLinkedList {
    items = [];
    position = 0;
    prevPos = 0;
    fitTo = null;

    constructor(items = []) {
        this.items = items;
    }

    current() {
        return this.items[this.position];
    }

    canMoveForward() {
        return (this.position + 1) < this.items.length;
    }

    moveForward() {
        if (this.canMoveForward()) {
            this.prevPos = this.position;
            this.position++;
            return true;
        }
        return false;
    }

    canMoveBackward() {
        return (this.position - 1) >= 0;
    }

    moveBackward() {
        if (this.canMoveBackward()) {
            this.prevPos = this.position;
            this.position--;
            return true;
        }
        return false;
    }

    reset() {
        this.prevPos = this.position;
        this.position = 0;
    }

    size() {
        return this.items.length;
    }
}
