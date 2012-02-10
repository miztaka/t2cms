var T2C = T2C || {};
T2C.Pager = function() {
	return {
		page: 1,
		limit: 0,
		total: 0,
		hit: 0,
		loadMeta: function() {
			var page = $('meta[name="X-T2C-Pager-page"]').attr('content');
			if (page) {
				this.page = parseInt(page,10);
			}
			var limit = $('meta[name="X-T2C-Pager-limit"]').attr('content');
			if (limit) {
				this.limit = parseInt(limit,10);
			}
			var total = $('meta[name="X-T2C-Pager-total"]').attr('content');
			if (total) {
				this.total = parseInt(total,10);
			}
			var hit = $('meta[name="X-T2C-Pager-hit"]').attr('content');
			if (hit) {
				this.hit = parseInt(hit,10);
			}
		},	
		first: function() {
			return (this.page - 1) * this.limit + 1;
		},
		last: function() {
			var max = this.first() + this.limit - 1;
			return max > this.total ? this.total : max;
		},
		hasNext: function() {
			return this.last() < this.total;
		},
		hasPrev: function() {
			return this.first() > 1;
		},
		numOfPages: function() {
			return Math.ceil(this.total/this.limit);
		}
	};
};
